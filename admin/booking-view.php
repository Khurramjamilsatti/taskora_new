<?php
require __DIR__ . '/_layout.php';
require_admin();

$id = (int)get('id');
$b = row('SELECT * FROM bookings WHERE id = ?', [$id]);
if (!$b) { flash('error', 'Booking not found.'); redirect('admin/bookings.php'); }

if (is_post()) {
    csrf_check();
    if (post('delete')) {
        foreach (['booking_offers', 'booking_messages', 'booking_feedback'] as $t) q("DELETE FROM $t WHERE booking_id = ?", [$id]);
        q('DELETE FROM bookings WHERE id = ?', [$id]);
        flash('success', 'Booking ' . $b['ref'] . ' deleted.');
        redirect('admin/bookings.php');
    }
    $status = post('status');
    if (!isset(booking_statuses()[$status])) $status = $b['status'];
    $prov = post('provider_id') !== '' ? (int)post('provider_id') : null;
    if ($prov && !val("SELECT id FROM users WHERE id = ? AND role = 'provider'", [$prov])) $prov = null;
    $deal = post('deal_amount') !== '' && is_numeric(post('deal_amount')) ? (float)post('deal_amount') : null;
    if ($status === 'requested') $prov = null; // an open request has no provider
    q('UPDATE bookings SET status = ?, provider_id = ?, deal_amount = ?, admin_note = ? WHERE id = ?', [$status, $prov, $deal, post('admin_note'), $id]);

    if ($prov && (int)$prov !== (int)$b['provider_id']) notify($prov, 'You were assigned a job', $b['ref'] . ' — ' . $b['service'], 'account/booking.php?id=' . $id);
    if ($status !== $b['status']) notify($b['customer_id'], 'Booking status updated', $b['ref'] . ' is now: ' . booking_statuses()[$status][0], 'account/booking.php?id=' . $id);
    flash('success', 'Booking updated.');
    redirect('admin/booking-view.php?id=' . $id);
}

$cust = $b['customer_id'] ? row('SELECT * FROM users WHERE id = ?', [$b['customer_id']]) : null;
$providers = rows("SELECT id, name, category, city, status FROM users WHERE role = 'provider' ORDER BY (category = ?) DESC, name", [$b['category']]);
$offers = rows('SELECT * FROM booking_offers WHERE booking_id = ? ORDER BY id', [$id]);
$msgs = rows('SELECT m.*, u.name FROM booking_messages m JOIN users u ON u.id = m.user_id WHERE booking_id = ? ORDER BY m.id', [$id]);
$fb = row('SELECT * FROM booking_feedback WHERE booking_id = ?', [$id]);

admin_start('Booking ' . $b['ref'], $b['service'] . ' · ' . $b['city'], '<a class="b b-ghost" href="bookings.php">' . icon('chev') . 'All bookings</a>');
require_once dirname(__DIR__) . '/account/_layout.php';
booking_steps($b['status']);
?>
<div class="row-2">
  <div>
    <div class="card">
      <div class="card-head"><div><h2>Request details</h2><p>Created <?= e(date('d M Y, g:i A', strtotime($b['created_at']))) ?> · Source: <?= e($b['source'] === 'web' ? 'Website form' : 'Customer portal') ?></p></div><?= status_pill($b['status']) ?></div>
      <div class="card-body"><div class="dl">
        <div><small>Customer</small><strong><?= e($b['name']) ?><?= $cust ? ' <span class="pill pill-green">Registered</span>' : '' ?></strong></div>
        <div><small>Phone</small><strong><a href="tel:<?= e($b['phone']) ?>"><?= e($b['phone']) ?></a></strong></div>
        <div><small>Email</small><strong><?= e($b['email'] ?: '—') ?></strong></div>
        <div><small>CNIC</small><strong><?= e($b['cnic'] ?: '—') ?></strong></div>
        <div><small>City</small><strong><?= e($b['city']) ?></strong></div>
        <div><small>Address</small><strong><?= e($b['address']) ?></strong></div>
        <div><small>Category</small><strong><?= e($b['category']) ?></strong></div>
        <div><small>Service</small><strong><?= e($b['service']) ?></strong></div>
        <div><small>Urgency</small><strong><?= e(ucfirst($b['urgency'])) ?></strong></div>
        <div><small>Preferred date</small><strong><?= $b['preferred_date'] ? e(date('d M Y', strtotime($b['preferred_date']))) : 'Flexible' ?></strong></div>
        <div><small>Budget</small><strong><?= $b['budget'] ? e(money($b['budget'])) : '—' ?></strong></div>
        <div><small>Payment</small><strong><?= e($b['payment_method'] ?: '—') ?><?= $b['quotation_required'] ? ' · Quote first' : '' ?></strong></div>
        <?php if ($b['map_link']): ?><div class="full"><small>Map</small><strong><a class="link-arrow" target="_blank" rel="noopener" href="<?= e($b['map_link']) ?>">Open location <?= icon('arrow-ur') ?></a></strong></div><?php endif; ?>
        <div class="full"><small>Description</small><strong style="font-weight:400"><?= nl($b['description']) ?></strong></div>
      </div></div>
    </div>

    <div class="card">
      <div class="card-head"><div><h2>Quotation trail</h2></div></div>
      <div class="card-body">
        <?php if (!$offers): ?><p class="muted" style="margin:0">No offers yet.</p><?php else: ?><ul class="trail">
          <?php foreach ($offers as $o): ?><li><span class="who <?= e($o['by_role']) ?>"><?= $o['by_role'] === 'customer' ? 'CU' : 'PR' ?></span><div><strong><?= e(money($o['amount'])) ?></strong> <span class="muted">· <?= e(time_ago($o['created_at'])) ?></span><?php if ($o['message']): ?><br><?= e($o['message']) ?><?php endif; ?></div></li><?php endforeach; ?>
        </ul><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div><h2>Chat log</h2><p><?= count($msgs) ?> message(s) — read-only</p></div></div>
      <div class="chat-log" style="max-height:340px;border-radius:0 0 18px 18px">
        <?php if (!$msgs): ?><div class="muted" style="margin:auto">No messages.</div><?php endif; ?>
        <?php foreach ($msgs as $m): ?><div class="msg<?= $m['role'] === 'provider' ? ' me' : '' ?>"><?= e($m['message']) ?><small><?= e($m['name']) ?> · <?= e(date('d M, g:i A', strtotime($m['created_at']))) ?></small></div><?php endforeach; ?>
      </div>
    </div>
  </div>

  <div>
    <form method="post" class="card">
      <?= csrf_field() ?>
      <div class="card-head"><div><h2>Manage</h2><p>Assign a provider or override the status</p></div></div>
      <div class="card-body">
        <div class="field"><label>Status</label><select class="input" name="status"><?php foreach (booking_statuses() as $k => $s): ?><option value="<?= $k ?>"<?= $b['status'] === $k ? ' selected' : '' ?>><?= e($s[0]) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Assigned provider</label><select class="input" name="provider_id"><option value="">— Not assigned —</option>
          <?php foreach ($providers as $p): ?><option value="<?= (int)$p['id'] ?>"<?= (int)$b['provider_id'] === (int)$p['id'] ? ' selected' : '' ?>><?= e($p['name']) ?> · <?= e($p['category'] ?: 'General') ?> · <?= e($p['city']) ?><?= $p['status'] !== 'active' ? ' (' . e($p['status']) . ')' : '' ?></option><?php endforeach; ?>
        </select></div>
        <div class="field"><label>Deal amount (PKR)</label><input class="input" type="number" min="0" name="deal_amount" value="<?= e($b['deal_amount'] !== null ? (int)$b['deal_amount'] : '') ?>"></div>
        <div class="field"><label>Internal note</label><textarea class="input" name="admin_note"><?= e($b['admin_note']) ?></textarea></div>
        <div class="form-actions"><button class="b b-gold"><?= icon('check') ?>Save changes</button></div>
      </div>
    </form>

    <?php if ($fb): ?>
    <div class="card"><div class="card-head"><div><h2>Customer feedback</h2></div></div>
      <div class="card-body">
        <div style="font-size:1.5rem;color:var(--gold)"><?= str_repeat('★', (int)$fb['rating']) ?></div>
        <div class="dl" style="margin-top:8px">
          <?php foreach (['professionalism', 'communication', 'punctuality', 'cleanliness', 'behaviour'] as $k): ?><div><small><?= e(ucfirst($k)) ?></small><strong><?= $fb[$k] ? (int)$fb[$k] . '/5' : '—' ?></strong></div><?php endforeach; ?>
          <div><small>Recommend</small><strong><?= $fb['recommend'] ? 'Yes' : 'No' ?></strong></div>
          <?php if ($fb['comments']): ?><div class="full"><small>Comments</small><strong style="font-weight:400"><?= e($fb['comments']) ?></strong></div><?php endif; ?>
        </div>
      </div></div>
    <?php endif; ?>

    <form method="post" class="card"><?= csrf_field() ?>
      <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;gap:10px"><span class="muted">Permanently delete this booking and its chat.</span>
        <button class="b b-danger" name="delete" value="1" data-confirm-submit="Delete booking <?= e($b['ref']) ?> permanently?"><?= icon('trash') ?>Delete</button></div>
    </form>
  </div>
</div>
<?php admin_end(); ?>
