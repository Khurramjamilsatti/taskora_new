<?php
require __DIR__ . '/_layout.php';
$u = require_user('provider');

$status = get('status');
$where = 'provider_id = ?'; $args = [$u['id']];
if (in_array($status, array_keys(booking_statuses()), true)) { $where .= ' AND status = ?'; $args[] = $status; }
$list = rows("SELECT * FROM bookings WHERE $where ORDER BY COALESCE(updated_at, created_at) DESC", $args);
$next = [
    'provider_joined' => 'Send quotation', 'quoted' => 'Wait for customer', 'deal_locked' => 'Start job',
    'in_progress' => 'Customer completes', 'completed' => 'Done', 'cancelled' => '—', 'requested' => '—',
];

portal_start('My jobs', 'Quote → customer accepts → you start → customer marks completed');
?>
<div class="card">
  <div class="card-head">
    <div class="tabs">
      <a href="jobs.php" class="<?= $status === '' ? 'active' : '' ?>">All statuses</a>
      <?php foreach (['provider_joined', 'quoted', 'deal_locked', 'in_progress', 'completed'] as $k): ?>
        <a href="?status=<?= $k ?>" class="<?= $status === $k ? 'active' : '' ?>"><?= e(booking_statuses()[$k][0]) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="filters"><input class="input search" placeholder="Search…" data-table-search="#jobTable"><a class="b" href="requests.php"><?= icon('inbox') ?>Find requests</a></div>
  </div>
  <?php if (!$list): ?><div class="empty"><?= icon('briefcase') ?>No jobs here yet. <a class="link-arrow" href="requests.php">Browse booking requests <?= icon('arrow') ?></a></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl" id="jobTable">
    <thead><tr><th>Reference</th><th>Service / Deal</th><th>Customer</th><th>City</th><th>Status</th><th>Next step</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($list as $b): ?>
        <tr>
          <td class="mono"><?= e($b['ref']) ?></td>
          <td><b><?= e($b['service']) ?></b><br><span class="muted"><?= $b['deal_amount'] ? e(money($b['deal_amount'])) : 'No quotation yet' ?></span></td>
          <td><?= e($b['name']) ?></td>
          <td><?= e($b['city']) ?></td>
          <td><?= status_pill($b['status']) ?></td>
          <td class="muted"><?= e($next[$b['status']] ?? '—') ?></td>
          <td class="actions"><a class="b b-ghost b-sm" href="booking.php?id=<?= (int)$b['id'] ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
<?php portal_end(); ?>
