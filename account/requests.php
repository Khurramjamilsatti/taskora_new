<?php
require __DIR__ . '/_layout.php';
$u = require_user('provider');

$list = rows("SELECT * FROM bookings WHERE status = 'requested' AND provider_id IS NULL ORDER BY FIELD(urgency,'emergency','urgent','normal'), id DESC");
portal_start('Open booking requests', 'Accept a request → quote → customer accepts deal → you start');
?>
<?php if ($u['status'] !== 'active'): ?>
  <div class="alert alert-info"><?= icon('clock') ?><span>Your profile is awaiting verification. You can view requests, and accepting unlocks after activation.</span></div>
<?php endif; ?>
<div class="card">
  <div class="card-head">
    <div><h2>Available requests</h2><p><?= count($list) ?> open · matching your category first</p></div>
    <div class="filters"><input class="input search" placeholder="Search reference, service, city…" data-table-search="#reqTable"></div>
  </div>
  <?php if (!$list): ?><div class="empty"><?= icon('inbox') ?>No open booking requests right now. Check back soon.</div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl" id="reqTable">
    <thead><tr><th>Reference</th><th>Service</th><th>City</th><th>Urgency</th><th>Preferred</th><th>Budget</th><th>Posted</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($list as $b): $match = $b['category'] === $u['category']; ?>
        <tr<?= $match ? ' style="box-shadow:inset 3px 0 0 var(--gold)"' : '' ?>>
          <td class="mono"><?= e($b['ref']) ?></td>
          <td><b><?= e($b['service']) ?></b><br><span class="muted"><?= e($b['category']) ?><?= $match ? ' · <span style="color:var(--gold-deep);font-weight:600">Your category</span>' : '' ?></span></td>
          <td><?= e($b['city']) ?></td>
          <td><?= $b['urgency'] === 'normal' ? '<span class="muted">Normal</span>' : '<span class="pill pill-red">' . e(ucfirst($b['urgency'])) . '</span>' ?></td>
          <td><?= $b['preferred_date'] ? e(date('d M', strtotime($b['preferred_date']))) : '—' ?></td>
          <td><?= $b['budget'] ? e(money($b['budget'])) : '—' ?></td>
          <td class="muted"><?= e(time_ago($b['created_at'])) ?></td>
          <td class="actions"><a class="b b-gold b-sm" href="booking.php?id=<?= (int)$b['id'] ?>">Review <?= icon('arrow') ?></a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
<?php portal_end(); ?>
