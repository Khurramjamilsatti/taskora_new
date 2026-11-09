<?php
require __DIR__ . '/_layout.php';
require_admin();

$k = [
    'bookings'  => (int)val('SELECT COUNT(*) FROM bookings'),
    'open'      => (int)val("SELECT COUNT(*) FROM bookings WHERE status NOT IN ('completed','cancelled')"),
    'revenue'   => (float)val("SELECT COALESCE(SUM(deal_amount),0) FROM bookings WHERE status = 'completed'"),
    'customers' => (int)val("SELECT COUNT(*) FROM users WHERE role = 'customer'"),
    'providers' => (int)val("SELECT COUNT(*) FROM users WHERE role = 'provider'"),
    'pending'   => (int)val("SELECT COUNT(*) FROM users WHERE role = 'provider' AND status = 'pending'"),
    'subs'      => (int)val("SELECT COUNT(*) FROM form_submissions WHERE status = 'new'"),
    'rating'    => val('SELECT ROUND(AVG(rating),1) FROM booking_feedback'),
];

// Bookings per day for the last 14 days.
$days = [];
for ($i = 13; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i days"))] = 0;
foreach (rows('SELECT DATE(created_at) d, COUNT(*) c FROM bookings WHERE created_at >= ? GROUP BY DATE(created_at)', [date('Y-m-d', strtotime('-13 days'))]) as $r) {
    if (isset($days[$r['d']])) $days[$r['d']] = (int)$r['c'];
}
$maxDay = max(1, max($days));

$byCat = rows('SELECT category, COUNT(*) c FROM bookings GROUP BY category ORDER BY c DESC LIMIT 6');
$maxCat = $byCat ? max(array_column($byCat, 'c')) : 1;
$recentB = rows('SELECT * FROM bookings ORDER BY id DESC LIMIT 6');
$recentS = rows('SELECT * FROM form_submissions ORDER BY id DESC LIMIT 6');
$statusCounts = [];
foreach (rows('SELECT status, COUNT(*) c FROM bookings GROUP BY status') as $r) $statusCounts[$r['status']] = (int)$r['c'];

admin_start('Dashboard', 'Welcome back, ' . current_admin()['name'] . ' — here is what is happening today.',
    '<a class="b b-gold" href="sections.php">' . icon('edit') . 'Edit website</a>');
?>
<div class="kpis">
  <div class="kpi dark"><span class="kpi-ic"><?= icon('calendar') ?></span><small>Total bookings</small><strong><?= $k['bookings'] ?></strong><span><?= $k['open'] ?> open right now</span></div>
  <div class="kpi gold"><span class="kpi-ic"><?= icon('wallet') ?></span><small>Completed value</small><strong><?= e(money($k['revenue'])) ?></strong><span>Avg. rating <?= $k['rating'] ? e($k['rating']) . '★' : '—' ?></span></div>
  <div class="kpi"><span class="kpi-ic"><?= icon('users') ?></span><small>Customers / Providers</small><strong><?= $k['customers'] ?> / <?= $k['providers'] ?></strong><span><?= $k['pending'] ?> provider(s) awaiting verification</span></div>
  <div class="kpi"><span class="kpi-ic"><?= icon('inbox') ?></span><small>New submissions</small><strong><?= $k['subs'] ?></strong><span>Forms waiting for review</span></div>
</div>

<div class="row-2">
  <div class="card">
    <div class="card-head"><div><h2>Bookings — last 14 days</h2><p>New requests per day</p></div></div>
    <div class="card-body">
      <div class="bars">
        <?php $i = 0; foreach ($days as $d => $c): ?>
          <div class="bar"><b><?= $c ?: '' ?></b><i style="--h:<?= max(3, round($c / $maxDay * 100)) ?>%;--i:<?= $i++ ?>"></i><?= e(date('d', strtotime($d))) ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-head"><div><h2>Top categories</h2><p>By number of bookings</p></div></div>
    <div class="card-body">
      <?php if (!$byCat): ?><p class="muted">No bookings yet.</p><?php endif; ?>
      <?php foreach ($byCat as $c): ?>
        <div class="hbar"><div><span style="display:inline"><?= e($c['category'] ?: 'Uncategorised') ?></span><b><?= (int)$c['c'] ?></b></div><span><i style="--w:<?= round($c['c'] / $maxCat * 100) ?>%"></i></span></div>
      <?php endforeach; ?>
      <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:16px">
        <?php foreach (booking_statuses() as $s => $l): ?><a href="bookings.php?status=<?= $s ?>"><?= status_pill($s) ?> <b style="font-size:.8rem"><?= (int)($statusCounts[$s] ?? 0) ?></b></a><?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="row-2e">
  <div class="card">
    <div class="card-head"><div><h2>Latest bookings</h2></div><a class="b b-ghost b-sm" href="bookings.php">View all</a></div>
    <div class="table-wrap"><table class="tbl"><tbody>
      <?php foreach ($recentB as $b): ?>
        <tr><td class="mono"><?= e($b['ref']) ?></td><td><b><?= e($b['service']) ?></b><br><span class="muted"><?= e($b['name']) ?> · <?= e($b['city']) ?></span></td><td><?= status_pill($b['status']) ?></td><td class="actions"><a class="b b-ghost b-sm" href="booking-view.php?id=<?= (int)$b['id'] ?>">Open</a></td></tr>
      <?php endforeach; ?>
      <?php if (!$recentB): ?><tr><td class="empty">No bookings yet.</td></tr><?php endif; ?>
    </tbody></table></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h2>Latest submissions</h2></div><a class="b b-ghost b-sm" href="submissions.php">View all</a></div>
    <div class="table-wrap"><table class="tbl"><tbody>
      <?php foreach ($recentS as $s): ?>
        <tr><td class="mono"><?= e($s['ref']) ?></td><td><b><?= e(ucfirst($s['form_type'])) ?></b><br><span class="muted"><?= e($s['name']) ?> · <?= e(time_ago($s['created_at'])) ?></span></td><td><span class="pill pill-<?= $s['status'] === 'new' ? 'gold' : ($s['status'] === 'resolved' ? 'green' : 'info') ?>"><?= e(ucwords(str_replace('_', ' ', $s['status']))) ?></span></td><td class="actions"><a class="b b-ghost b-sm" href="submissions.php?view=<?= (int)$s['id'] ?>">Open</a></td></tr>
      <?php endforeach; ?>
      <?php if (!$recentS): ?><tr><td class="empty">No submissions yet.</td></tr><?php endif; ?>
    </tbody></table></div>
  </div>
</div>
<?php admin_end(); ?>
