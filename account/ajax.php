<?php
/**
 * Portal actions. Every booking transition is validated here against
 * the current status and the viewer's role/ownership.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$u = current_user();
if (!$u) json_out(['ok' => false, 'message' => 'Please sign in again.'], 401);

$action = $_SERVER['REQUEST_METHOD'] === 'GET' ? get('action') : post('action');

/* ---------- Chat polling (GET, read-only) ---------- */
if ($action === 'chat_poll') {
    $b = row('SELECT * FROM bookings WHERE id = ?', [(int)get('booking_id')]);
    if (!$b || !in_array((int)$u['id'], [(int)$b['customer_id'], (int)$b['provider_id']], true)) json_out(['ok' => false], 403);
    $msgs = rows('SELECT m.*, u.name FROM booking_messages m JOIN users u ON u.id = m.user_id WHERE m.booking_id = ? AND m.id > ? ORDER BY m.id', [$b['id'], (int)get('after')]);
    json_out(['ok' => true, 'messages' => array_map(function ($m) use ($u) {
        return ['id' => (int)$m['id'], 'message' => $m['message'], 'who' => $m['name'], 'mine' => (int)$m['user_id'] === (int)$u['id'], 'time' => date('d M, g:i A', strtotime($m['created_at']))];
    }, $msgs)]);
}

if (!is_post()) json_out(['ok' => false], 405);
csrf_check();

if ($action === 'notif_read_all') {
    q('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [$u['id']]);
    json_out(['ok' => true]);
}

$id = (int)post('id', post('booking_id'));
$b = row('SELECT * FROM bookings WHERE id = ?', [$id]);
if (!$b) json_out(['ok' => false, 'message' => 'Booking not found.'], 404);

$isCust = $u['role'] === 'customer' && (int)$b['customer_id'] === (int)$u['id'];
$isProv = $u['role'] === 'provider' && (int)$b['provider_id'] === (int)$u['id'];
$link = 'account/booking.php?id=' . $b['id'];

function amount_or_fail($required = true)
{
    $a = post('amount');
    if ($a === '' && !$required) return null;
    if (!is_numeric($a) || $a <= 0 || $a > 100000000) json_out(['ok' => false, 'message' => 'Enter a valid amount.'], 422);
    return round((float)$a, 2);
}
function set_status($id, $status, $extra = [])
{
    $sql = 'UPDATE bookings SET status = ?'; $args = [$status];
    foreach ($extra as $k => $v) { $sql .= ", $k = ?"; $args[] = $v; }
    $args[] = $id;
    q($sql . ' WHERE id = ?', $args);
}

switch ($action) {

    /* ----- Provider ----- */
    case 'accept':
        if ($u['role'] !== 'provider' || $u['status'] !== 'active') json_out(['ok' => false, 'message' => 'Only verified providers can accept jobs.'], 403);
        $amt = amount_or_fail(false);
        // Atomic claim: only succeeds if still open.
        $st = q("UPDATE bookings SET provider_id = ?, status = ? WHERE id = ? AND status = 'requested' AND provider_id IS NULL", [$u['id'], $amt ? 'quoted' : 'provider_joined', $id]);
        if (!$st->rowCount()) json_out(['ok' => false, 'message' => 'Another provider already accepted this request.'], 409);
        if ($amt) {
            q('INSERT INTO booking_offers (booking_id, by_role, amount, message) VALUES (?,?,?,?)', [$id, 'provider', $amt, mb_substr(post('message'), 0, 500)]);
            q('UPDATE bookings SET deal_amount = ? WHERE id = ?', [$amt, $id]);
        }
        notify($b['customer_id'], 'A provider accepted your request', $u['name'] . ($amt ? ' sent a quotation of ' . money($amt) . '.' : ' joined ' . $b['ref'] . '.'), $link);
        json_out(['ok' => true, 'message' => 'Booking accepted. It is now in My Jobs.', 'redirect' => base_url($link)]);

    case 'quote':
        if (!$isProv || !in_array($b['status'], ['provider_joined', 'quoted'], true)) json_out(['ok' => false, 'message' => 'You cannot quote on this booking now.'], 403);
        $amt = amount_or_fail();
        q('INSERT INTO booking_offers (booking_id, by_role, amount, message) VALUES (?,?,?,?)', [$id, 'provider', $amt, mb_substr(post('message'), 0, 500)]);
        set_status($id, 'quoted', ['deal_amount' => $amt]);
        notify($b['customer_id'], 'Quotation received', $u['name'] . ' quoted ' . money($amt) . ' for ' . $b['ref'] . '.', $link);
        json_out(['ok' => true, 'message' => 'Quotation sent to the customer.']);

    case 'start':
        if (!$isProv || $b['status'] !== 'deal_locked') json_out(['ok' => false, 'message' => 'This job cannot be started yet.'], 403);
        set_status($id, 'in_progress');
        notify($b['customer_id'], 'Work has started', 'Your provider has started ' . $b['ref'] . '.', $link);
        json_out(['ok' => true, 'message' => 'Job started. The customer has been notified.']);

    case 'release':
        if (!$isProv || !in_array($b['status'], ['provider_joined', 'quoted', 'deal_locked'], true)) json_out(['ok' => false, 'message' => 'This job cannot be released now.'], 403);
        set_status($id, 'requested', ['provider_id' => null, 'deal_amount' => null]);
        notify($b['customer_id'], 'Provider released your booking', $b['ref'] . ' is open again for other providers.', $link);
        json_out(['ok' => true, 'message' => 'Job released back to open requests.', 'redirect' => base_url('account/jobs.php')]);

    /* ----- Customer ----- */
    case 'counter':
        if (!$isCust || !in_array($b['status'], ['provider_joined', 'quoted'], true)) json_out(['ok' => false, 'message' => 'You cannot send a budget now.'], 403);
        $amt = amount_or_fail();
        q('INSERT INTO booking_offers (booking_id, by_role, amount, message) VALUES (?,?,?,?)', [$id, 'customer', $amt, mb_substr(post('message'), 0, 500)]);
        set_status($id, 'provider_joined', ['budget' => $amt]);
        notify($b['provider_id'], 'Customer sent a budget', money($amt) . ' for ' . $b['ref'] . ' — reply with a quotation.', $link);
        json_out(['ok' => true, 'message' => 'Budget sent. The provider will reply with a quotation.']);

    case 'accept_deal':
        if (!$isCust || $b['status'] !== 'quoted') json_out(['ok' => false, 'message' => 'There is no quotation to accept.'], 403);
        set_status($id, 'deal_locked');
        notify($b['provider_id'], 'Deal accepted', 'The customer accepted your quotation for ' . $b['ref'] . '. Chat is open.', $link);
        json_out(['ok' => true, 'message' => 'Deal locked — chat is open and the provider can start.']);

    case 'complete':
        if (!$isCust || $b['status'] !== 'in_progress') json_out(['ok' => false, 'message' => 'This job is not in progress.'], 403);
        set_status($id, 'completed');
        notify($b['provider_id'], 'Job completed', 'The customer marked ' . $b['ref'] . ' as completed.', $link);
        json_out(['ok' => true, 'message' => 'Job completed. Please leave feedback for your provider.']);

    case 'cancel':
        if (!$isCust || !in_array($b['status'], ['requested', 'provider_joined', 'quoted'], true)) json_out(['ok' => false, 'message' => 'This booking can no longer be cancelled.'], 403);
        set_status($id, 'cancelled');
        if ($b['provider_id']) notify($b['provider_id'], 'Booking cancelled', $b['ref'] . ' was cancelled by the customer.', $link);
        json_out(['ok' => true, 'message' => 'Booking cancelled.']);

    case 'feedback':
        if (!$isCust || $b['status'] !== 'completed') json_out(['ok' => false, 'message' => 'Feedback is available after completion.'], 403);
        if (val('SELECT id FROM booking_feedback WHERE booking_id = ?', [$id])) json_out(['ok' => false, 'message' => 'Feedback already submitted.'], 409);
        $r = (int)post('rating');
        if ($r < 1 || $r > 5) json_out(['ok' => false, 'message' => 'Please set at least an overall star rating.'], 422);
        $sub = function ($k) { $v = (int)post($k); return ($v >= 1 && $v <= 5) ? $v : null; };
        q('INSERT INTO booking_feedback (booking_id, rating, professionalism, communication, punctuality, cleanliness, behaviour, recommend, comments) VALUES (?,?,?,?,?,?,?,?,?)',
          [$id, $r, $sub('professionalism'), $sub('communication'), $sub('punctuality'), $sub('cleanliness'), $sub('behaviour'), post('recommend') === '0' ? 0 : 1, mb_substr(post('comments'), 0, 1000)]);
        notify($b['provider_id'], 'New rating received', $r . '★ for ' . $b['ref'] . '.', $link);
        json_out(['ok' => true, 'message' => 'Thank you — your feedback was submitted.']);

    /* ----- Chat ----- */
    case 'chat_send':
        if ((!$isCust && !$isProv) || !in_array($b['status'], ['deal_locked', 'in_progress', 'completed'], true)) json_out(['ok' => false, 'message' => 'Chat is not available for this booking.'], 403);
        $msg = trim(post('message'));
        if ($msg === '' || mb_strlen($msg) > 1000) json_out(['ok' => false, 'message' => 'Message must be 1–1000 characters.'], 422);
        if (!throttle('chat', 20, 60)) json_out(['ok' => false, 'message' => 'You are sending messages too quickly.'], 429);
        q('INSERT INTO booking_messages (booking_id, user_id, role, message) VALUES (?,?,?,?)', [$id, $u['id'], $u['role'], $msg]);
        json_out(['ok' => true]);
}

json_out(['ok' => false, 'message' => 'Unknown action.'], 400);
