<?php
require __DIR__ . '/_layout.php';
$u = require_user('customer');
$cats = rows('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order');

portal_start('Book a service', 'Choose a service to open the booking form');
?>
<div class="card">
  <div class="card-head">
    <div><h2>All services</h2><p><?= count($cats) ?> categories · tap a service to book</p></div>
    <div class="filters"><input class="input search" placeholder="Search services…" id="svcFilter"></div>
  </div>
  <div class="card-body">
    <div id="svcList" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px">
      <?php foreach ($cats as $c): ?>
        <div class="kpi svc-box" style="padding:20px">
          <span class="kpi-ic"><?= icon($c['icon'] ?: 'spark') ?></span>
          <small><?= e($c['code']) ?></small>
          <strong style="font-size:1.05rem;font-family:var(--f-body);margin:6px 50px 12px 0"><?= e($c['title']) ?></strong>
          <div style="display:flex;flex-direction:column;gap:4px">
            <?php foreach (lines($c['items']) as $it): ?>
              <a class="svc-link" style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border-radius:8px;background:var(--db-surface-2);font-size:.86rem" href="../forms.php?type=booking&category=<?= urlencode($c['title']) ?>&service=<?= urlencode($it) ?>"><?= e($it) ?><span style="color:var(--green);font-weight:600">Book →</span></a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="empty" id="svcNone" style="display:none"><?= icon('search') ?>No services match your search.</div>
  </div>
</div>
<script>
document.getElementById('svcFilter').addEventListener('input', function () {
  var q = this.value.toLowerCase(), any = false;
  document.querySelectorAll('.svc-box').forEach(function (box) {
    var hit = false;
    box.querySelectorAll('.svc-link').forEach(function (a) { var m = !q || a.textContent.toLowerCase().indexOf(q) > -1 || box.textContent.toLowerCase().indexOf(q) > -1 && !q; a.style.display = m ? '' : 'none'; if (m) hit = true; });
    if (!hit && box.querySelector('strong').textContent.toLowerCase().indexOf(q) > -1) { hit = true; box.querySelectorAll('.svc-link').forEach(function (a) { a.style.display = ''; }); }
    box.style.display = hit ? '' : 'none'; if (hit) any = true;
  });
  document.getElementById('svcNone').style.display = any ? 'none' : 'block';
});
</script>
<?php portal_end(); ?>
