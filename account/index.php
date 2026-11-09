<?php
require __DIR__ . '/_layout.php';
$u = require_user();

if ($u['role'] === 'provider') {
    $open   = (int)val("SELECT COUNT(*) FROM bookings WHERE status = 'requested' AND provider_id IS NULL");
    $active = (int)val("SELECT COUNT(*) FROM bookings WHERE provider_id = ? AND status IN ('provider_joined','quoted','deal_locked','in_progress')", [$u['id']]);
    $all    = (int)val('SELECT COUNT(*) FROM bookings WHERE provider_id = ?', [$u['id']]);
    $earned = (float)val("SELECT COALESCE(SUM(deal_amount),0) FROM bookings WHERE provider_id = ? AND status = 'completed'", [$u['id']]);
    $rating = val('SELECT ROUND(AVG(f.rating),1) FROM booking_feedback f JOIN bookings b ON b.id = f.booking_id WHERE b.provider_id = ?', [$u['id']]);
    $recent = rows('SELECT * FROM bookings WHERE provider_id = ? ORDER BY COALESCE(updated_at, created_at) DESC LIMIT 6', [$u['id']]);

    portal_start('Provider workspace', 'Welcome, ' . $u['name']);
    if ($u['status'] === 'pending'): ?>
      <div class="alert alert-info"><?= icon('clock') ?><span><b>Verification in progress.</b> Your profile is being reviewed. You can browse requests now; accepting jobs unlocks once you are activated.</span></div>
    <?php endif; ?>
    <div class="kpis">
      <div class="kpi dark"><span class="kpi-ic"><?= icon('inbox') ?></span><small>Open requests</small><strong><?= $open ?></strong><span>Available to accept</span></div>
      <div class="kpi"><span class="kpi-ic"><?= icon('briefcase') ?></span><small>Active jobs</small><strong><?= $active ?></strong><span>Assigned or in progress</span></div>
      <div class="kpi gold"><span class="kpi-ic"><?= icon('wallet') ?></span><small>Completed earnings</small><strong><?= e(money($earned)) ?></strong><span><?= $all ?> jobs in total</span></div>
      <div class="kpi"><span class="kpi-ic"><?= icon('star') ?></span><small>Average rating</small><strong><?= $rating ? e($rating) . '★' : '—' ?></strong><span>Status: <?= e(ucfirst($u['status'])) ?></span></div>
    </div>
    <div class="row-2">
      <div class="card">
        <div class="card-head"><div><h2>Recent jobs</h2><p>Your latest assigned work</p></div><a class="b b-ghost b-sm" href="jobs.php">View all</a></div>
        <?php if (!$recent): ?><div class="empty"><?= icon('briefcase') ?>No jobs yet. <a class="link-arrow" href="requests.php">Browse open requests <?= icon('arrow') ?></a></div>
        <?php else: ?><div class="table-wrap"><table class="tbl"><thead><tr><th>Reference</th><th>Service</th><th>City</th><th>Status</th><th></th></tr></thead><tbody>
          <?php foreach ($recent as $b): ?><tr><td class="mono"><?= e($b['ref']) ?></td><td><?= e($b['service']) ?></td><td><?= e($b['city']) ?></td><td><?= status_pill($b['status']) ?></td><td class="actions"><a class="b b-ghost b-sm" href="booking.php?id=<?= (int)$b['id'] ?>">Open</a></td></tr><?php endforeach; ?>
        </tbody></table></div><?php endif; ?>
      </div>
      <div class="card">
        <div class="card-head"><div><h2>Booking workflow</h2><p>How jobs move from request to completion</p></div></div>
        <div class="card-body">
          <ul class="trail">
            <li><span class="who provider">1</span><div><b>Accept a request</b><br><span class="muted">Claim an open job, optionally with a first quotation.</span></div></li>
            <li><span class="who provider">2</span><div><b>Send your quotation</b><br><span class="muted">The customer can accept or counter your price.</span></div></li>
            <li><span class="who customer">3</span><div><b>Deal locked</b><br><span class="muted">Chat opens and you can start the job.</span></div></li>
            <li><span class="who customer">4</span><div><b>Customer completes</b><br><span class="muted">They mark it done and leave a rating.</span></div></li>
          </ul>
          <a class="b" style="margin-top:14px" href="requests.php"><?= icon('inbox') ?>Review booking requests</a>
        </div>
      </div>
    </div>
<?php
    portal_end();
    exit;
}

/* ---------- Customer overview ---------- */
$total   = (int)val('SELECT COUNT(*) FROM bookings WHERE customer_id = ?', [$u['id']]);
$active  = (int)val("SELECT COUNT(*) FROM bookings WHERE customer_id = ? AND status NOT IN ('completed','cancelled')", [$u['id']]);
$spent   = (float)val("SELECT COALESCE(SUM(deal_amount),0) FROM bookings WHERE customer_id = ? AND status = 'completed'", [$u['id']]);
$need    = rows("SELECT b.* FROM bookings b LEFT JOIN booking_feedback f ON f.booking_id = b.id WHERE b.customer_id = ? AND (b.status = 'quoted' OR (b.status = 'in_progress') OR (b.status = 'completed' AND f.id IS NULL)) ORDER BY b.id DESC LIMIT 5", [$u['id']]);
$recent  = rows('SELECT b.*, p.name AS provider_name FROM bookings b LEFT JOIN users p ON p.id = b.provider_id WHERE b.customer_id = ? ORDER BY b.id DESC LIMIT 6', [$u['id']]);
$catCount = (int)val('SELECT COUNT(*) FROM categories WHERE is_active = 1');

portal_start('Overview', 'Welcome, ' . $u['name']);
?>
<div class="kpis">
  <div class="kpi dark"><span class="kpi-ic"><?= icon('calendar') ?></span><small>Total bookings</small><strong><?= $total ?></strong><span><?= $active ?> active right now</span></div>
  <div class="kpi gold"><span class="kpi-ic"><?= icon('wallet') ?></span><small>Total spent</small><strong><?= e(money($spent)) ?></strong><span>Completed jobs</span></div>
  <div class="kpi"><span class="kpi-ic"><?= icon('grid') ?></span><small>Categories</small><strong><?= $catCount ?></strong><span>Service groups available</span></div>
  <div class="kpi"><span class="kpi-ic"><?= icon('bell') ?></span><small>Needs your action</small><strong><?= count($need) ?></strong><span>Quotes, completions, feedback</span></div>
</div>

<?php if ($need): ?>
<div class="card">
  <div class="card-head"><div><h2>Action needed</h2><p>Bookings waiting on you</p></div></div>
  <div class="table-wrap"><table class="tbl"><tbody>
    <?php foreach ($need as $b):
      $what = $b['status'] === 'quoted' ? 'Review and accept the quotation' : ($b['status'] === 'in_progress' ? 'Mark the job completed when satisfied' : 'Leave feedback for your provider'); ?>
      <tr><td class="mono"><?= e($b['ref']) ?></td><td><b><?= e($b['service']) ?></b><br><span class="muted"><?= e($what) ?></span></td><td><?= status_pill($b['status']) ?></td><td class="actions"><a class="b b-gold b-sm" href="booking.php?id=<?= (int)$b['id'] ?>">Open <?= icon('arrow') ?></a></td></tr>
    <?php endforeach; ?>
  </tbody></table></div>
</div>
<?php endif; ?>

<div class="row-2">
  <div class="card">
    <div class="card-head"><div><h2>Recent bookings</h2><p>Your latest requests</p></div><a class="b b-ghost b-sm" href="bookings.php">View all</a></div>
    <?php if (!$recent): ?><div class="empty"><?= icon('calendar') ?>No bookings yet.<br><a class="b" style="margin-top:14px" href="services.php"><?= icon('plus') ?>Book your first service</a></div>
    <?php else: ?><div class="table-wrap"><table class="tbl"><thead><tr><th>Reference</th><th>Service</th><th>Provider</th><th>Status</th><th></th></tr></thead><tbody>
      <?php foreach ($recent as $b): ?><tr><td class="mono"><?= e($b['ref']) ?></td><td><?= e($b['service']) ?></td><td><?= e($b['provider_name'] ?: '—') ?></td><td><?= status_pill($b['status']) ?></td><td class="actions"><a class="b b-ghost b-sm" href="booking.php?id=<?= (int)$b['id'] ?>">Open</a></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
  </div>
  <div class="card">
    <div class="card-head"><div><h2>Quick actions</h2><p>From request to completed job</p></div></div>
    <div class="card-body" style="display:grid;gap:10px">
      <a class="b" href="services.php"><?= icon('grid') ?>Browse all services</a>
      <a class="b b-ghost" href="bookings.php"><?= icon('list') ?>View booking list</a>
      <a class="b b-ghost" href="../forms.php?type=complaint"><?= icon('doc') ?>Raise a complaint</a>
      <a class="b b-ghost" href="../forms.php?type=feedback"><?= icon('star') ?>Share feedback</a>
    </div>
  </div>
</div>
<?php portal_end(); ?>
