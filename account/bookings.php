<?php
require __DIR__ . '/_layout.php';
$u = require_user('customer');

$status = get('status');
$valid = array_keys(booking_statuses());
$where = 'b.customer_id = ?'; $args = [$u['id']];
if (in_array($status, $valid, true)) { $where .= ' AND b.status = ?'; $args[] = $status; }
$list = rows("SELECT b.*, p.name AS provider_name, p.phone AS provider_phone FROM bookings b LEFT JOIN users p ON p.id = b.provider_id WHERE $where ORDER BY b.id DESC", $args);
$counts = [];
foreach (rows('SELECT status, COUNT(*) c FROM bookings WHERE customer_id = ? GROUP BY status', [$u['id']]) as $r) $counts[$r['status']] = $r['c'];

portal_start('My bookings', 'Request → provider → quotation → deal → start → you complete');
?>
<div class="card">
  <div class="card-head">
    <div class="tabs">
      <a href="bookings.php" class="<?= $status === '' ? 'active' : '' ?>">All <span class="n"><?= array_sum($counts) ?></span></a>
      <?php foreach (booking_statuses() as $k => $s): ?>
        <a href="?status=<?= e($k) ?>" class="<?= $status === $k ? 'active' : '' ?>"><?= e($s[0]) ?> <span class="n"><?= (int)($counts[$k] ?? 0) ?></span></a>
      <?php endforeach; ?>
    </div>
    <div class="filters"><input class="input search" placeholder="Search reference, service, provider…" data-table-search="#bkTable"><a class="b" href="services.php"><?= icon('plus') ?>New booking</a></div>
  </div>
  <?php if (!$list): ?>
    <div class="empty"><?= icon('calendar') ?>No bookings here yet.</div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl" id="bkTable">
    <thead><tr><th>Reference</th><th>Service</th><th>Urgency</th><th>Date</th><th>Budget / Deal</th><th>Provider</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($list as $b): ?>
        <tr>
          <td class="mono"><?= e($b['ref']) ?></td>
          <td><b><?= e($b['service']) ?></b><br><span class="muted"><?= e($b['category']) ?></span></td>
          <td><?= $b['urgency'] === 'normal' ? '<span class="muted">Normal</span>' : '<span class="pill pill-red">' . e(ucfirst($b['urgency'])) . '</span>' ?></td>
          <td><?= $b['preferred_date'] ? e(date('d M Y', strtotime($b['preferred_date']))) : '<span class="muted">Flexible</span>' ?></td>
          <td><?= $b['deal_amount'] ? '<b>' . e(money($b['deal_amount'])) . '</b>' : ($b['budget'] ? e(money($b['budget'])) : '—') ?></td>
          <td><?= e($b['provider_name'] ?: 'Not assigned yet') ?></td>
          <td><?= status_pill($b['status']) ?></td>
          <td class="actions">
            <a class="b b-ghost b-sm" href="booking.php?id=<?= (int)$b['id'] ?>"><?= icon('eye') ?>View</a>
            <?php if (in_array($b['status'], ['requested', 'provider_joined', 'quoted'], true)): ?>
              <button class="b b-danger b-sm" data-action="cancel" data-id="<?= (int)$b['id'] ?>" data-confirm="Cancel this booking?">Cancel</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
<?php portal_end(); ?>
