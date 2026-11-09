<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require dirname(__DIR__) . '/includes/forms_config.php';
if (!is_post()) json_out(['ok' => false], 405);
csrf_check();

$defs = form_defs();
$type = post('form_type');
if (!isset($defs[$type])) json_out(['ok' => false, 'message' => 'Unknown form.'], 422);
$def = $defs[$type];

// Honeypot: silently accept bots without storing.
if (post('website') !== '') json_out(['ok' => true, 'html' => '<div class="success-card"><h3>Thank you.</h3></div>']);
if (!throttle('form_' . $type, 6, 300)) json_out(['ok' => false, 'message' => 'Too many submissions. Please wait a few minutes.'], 429);

list($data, $errors) = validate_form($def);
if ($errors) json_out(['ok' => false, 'errors' => $errors, 'message' => 'Please check the highlighted fields.'], 422);

$user = current_user();
$ref = make_ref($def['prefix']);

if ($type === 'booking') {
    q('INSERT INTO bookings (ref, customer_id, name, phone, email, cnic, city, address, map_link, category, service, description, urgency, preferred_date, budget, quotation_required, payment_method, source)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
        $ref, $user && $user['role'] === 'customer' ? $user['id'] : null,
        $data['name'], $data['phone'], $data['email'] ?: null, $data['cnic'] ?: null, $data['city'], $data['address'], $data['map_link'] ?: null,
        $data['category'], $data['service'], $data['description'], $data['urgency'] ?: 'normal',
        $data['preferred_date'] ?: null, $data['budget'] !== '' ? $data['budget'] : null, (int)$data['quotation_required'], $data['payment_method'], 'web',
    ]);
    // Alert matching providers.
    foreach (rows("SELECT id FROM users WHERE role = 'provider' AND status = 'active' AND (category = ? OR category IS NULL)", [$data['category']]) as $p) {
        notify($p['id'], 'New booking request', $data['service'] . ' in ' . $data['city'], 'account/requests.php');
    }
} else {
    q('INSERT INTO form_submissions (ref, form_type, user_id, name, email, phone, data, ip) VALUES (?,?,?,?,?,?,?,?)', [
        $ref, $type, $user ? $user['id'] : null,
        $data['name'] ?? ($data['company_name'] ?? null), $data['email'] ?? null, $data['phone'] ?? null,
        json_encode($data, JSON_UNESCAPED_UNICODE), client_ip(),
    ]);
}

// Optional email alert to admin (works when mail() is configured on the server).
$to = setting('email');
if ($to && function_exists('mail')) {
    $body = "New {$def['title']} submission ({$ref})\n\n";
    foreach ($data as $k => $v) $body .= ucwords(str_replace('_', ' ', $k)) . ': ' . $v . "\n";
    @mail($to, '[' . setting('site_name') . "] {$def['title']} — {$ref}", $body, 'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
}

$messages = [
    'booking'   => 'Your request is live. A verified professional will review it and share a quotation shortly.',
    'company'   => 'Thank you — our corporate team will review your profile within 2 business days.',
    'feedback'  => 'Thank you — your rating has been recorded and shared with our quality team.',
    'complaint' => 'Your complaint has been logged. Our resolution team aims to respond within 24 hours.',
    'refund'    => 'Your refund request has been received. We will respond within 3 business days.',
    'insurance' => 'Your claim has been submitted and forwarded to our insurance partner for review.',
    'contact'   => 'Thanks for reaching out — our team will reply within one business day.',
];

$html = '<div class="success-card"><div class="success-ic">' . icon('check') . '</div>'
      . '<h3>Submitted successfully</h3><p style="color:var(--muted);max-width:460px;margin:0 auto">' . e($messages[$type]) . '</p>'
      . '<div class="ref-code">Reference: ' . e($ref) . '</div>'
      . '<div style="margin-top:30px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">'
      . '<a class="btn btn-dark" href="' . e(base_url('index.php')) . '"><span>Back to home</span></a>'
      . ($type === 'booking' && !$user ? '<a class="btn btn-gold" href="' . e(base_url('account/register.php')) . '"><span>Create account to track</span>' . icon('arrow') . '</a>' : '')
      . ($type === 'booking' && $user ? '<a class="btn btn-gold" href="' . e(base_url('account/bookings.php')) . '"><span>Track my bookings</span>' . icon('arrow') . '</a>' : '')
      . '</div></div>';

json_out(['ok' => true, 'ref' => $ref, 'message' => $messages[$type], 'html' => $html]);
