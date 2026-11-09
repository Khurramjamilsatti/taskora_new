<?php
require __DIR__ . '/includes/bootstrap.php';

$hero = section('cat_hero');      $ver = section('cat_verification');  $browse = section('cat_browse');
$prof = section('cat_profile');   $qual = section('cat_quality');      $cta = section('cat_cta');
$groups = rows('SELECT * FROM catalogue_groups WHERE is_active = 1 ORDER BY sort_order, id');

render_head('Master Professional Catalogue — 300+ Verified Specialisations | ' . setting('site_name', 'Taskora'),
    $hero ? $hero['description'] : '', ['body' => 'has-hero']);
render_header();
?>
<main>
<section class="page-hero grain">
  <div class="orb orb-green"></div><div class="orb orb-gold"></div>
  <div class="container">
    <div class="crumbs" data-reveal><a href="<?= e(base_url('index.php')) ?>">Home</a><?= icon('chev') ?><span>Catalogue</span></div>
    <?php if ($hero): ?>
      <span class="eyebrow eyebrow-gold" data-reveal><?= e($hero['tag']) ?></span>
      <h1 class="display split-words"><?= emph($hero['title'], $hero['emphasis']) ?></h1>
      <p class="lede" data-reveal data-delay="3"><?= e($hero['description']) ?></p>
      <div class="hero-stat-row" data-stagger>
        <?php foreach (g($hero['data'], 'stats') as $s): ?><div><strong data-count="<?= e($s['value']) ?>"><?= e($s['value']) ?></strong><span><?= e($s['label']) ?></span></div><?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if (on($ver)): ?>
<section class="section paper2">
  <div class="container">
    <?php section_head($ver, ['align' => 'center']); ?>
    <div class="verify-grid" data-stagger>
      <?php foreach (g($ver['data'], 'checks') as $i => $c): ?>
        <div class="verify-item"><b><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></b><?= e($c) ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" id="browse">
  <div class="container">
    <div class="svc-toolbar">
      <?php section_head($browse); ?>
      <div class="svc-search" data-reveal><?= icon('search') ?><input type="search" id="catSearch" placeholder="Search a profession — e.g. Electrician…" aria-label="Search catalogue"></div>
    </div>
    <div id="accList">
      <?php foreach ($groups as $i => $gp):
        $bodyLines = lines($gp['body']);
        $total = 0;
      ?>
      <div class="acc<?= $i === 0 ? ' open' : '' ?>" data-reveal>
        <button class="acc-head" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
          <span class="acc-tone tone-<?= e($gp['tone']) ?>"></span>
          <div><h3><?= e($gp['name']) ?></h3><small><?= e($gp['count_label']) ?></small></div>
          <span class="acc-plus"><?= icon('plus') ?></span>
        </button>
        <div class="acc-body"><div><div class="acc-inner">
          <?php foreach ($bodyLines as $ln):
            $title = ''; $chips = $ln;
            if (strpos($ln, ':') !== false) { list($title, $chips) = array_map('trim', explode(':', $ln, 2)); }
            $chips = array_filter(array_map('trim', explode(',', $chips)));
          ?>
            <div class="sub-group">
              <?php if ($title): ?><h5><?= e($title) ?></h5><?php endif; ?>
              <div class="chip-cloud"><?php foreach ($chips as $ch): ?><a class="chip" href="<?= e(base_url('forms.php?type=booking&service=' . urlencode($ch))) ?>"><?= e($ch) ?></a><?php endforeach; ?></div>
            </div>
          <?php endforeach; ?>
          <?php if ($gp['note']): ?><div class="acc-note"><?= e($gp['note']) ?></div><?php endif; ?>
        </div></div></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="svc-empty" id="catEmpty">No professions match your search.</div>
  </div>
</section>

<?php if (on($prof)): $pd = $prof['data']; ?>
<section class="section paper2">
  <div class="container profile-wrap">
    <div><?php section_head($prof); ?>
      <a class="btn btn-dark magnetic" href="<?= e(base_url('account/register.php?role=provider')) ?>" data-reveal><span>Create your verified profile</span><?= icon('arrow') ?></a>
    </div>
    <div class="profile-card" data-reveal="right" data-tilt>
      <div class="pc-top">
        <span class="pc-ava"><?= e(g($pd, 'initials', '')) ?></span>
        <div><h3><?= e(g($pd, 'name', '')) ?></h3><small><?= e(g($pd, 'role', '')) ?></small></div>
        <span class="pc-verified"><?= icon('shield') ?>Verified</span>
      </div>
      <div class="pc-fields">
        <?php foreach (g($pd, 'fields') as $f): $tone = $f['tone'] === 'green' ? 'tone-g' : ($f['tone'] === 'gold' ? 'tone-y' : ''); ?>
          <div class="pc-field"><small><?= e($f['label']) ?></small><strong class="<?= $tone ?>"><?= e($f['value']) ?></strong></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($qual)): ?>
<section class="section dark grain">
  <div class="container" style="position:relative">
    <?php section_head($qual, ['align' => 'center', 'light' => true]); ?>
    <div class="quality-grid" data-stagger>
      <?php foreach (g($qual['data'], 'items') as $q): ?><div class="q-item"><?= icon('check-circle') ?><?= e($q) ?></div><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($cta)): $cd = $cta['data']; ?>
<section class="section">
  <div class="container">
    <div class="cta-band grain" data-reveal="scale" style="grid-template-columns:1fr">
      <div class="orb orb-green"></div><div class="orb orb-gold"></div>
      <div style="position:relative;text-align:center">
        <h2 class="cta-title split-words" style="max-width:900px;margin-left:auto;margin-right:auto"><?= emph($cta['title'], $cta['emphasis']) ?></h2>
        <div class="cta-btns" style="justify-content:center">
          <a class="btn btn-gold magnetic" href="<?= e(base_url(g($cd, 'primary_href', 'forms.php?type=booking'))) ?>"><span><?= e(g($cd, 'primary_label', 'Book a Service')) ?></span><?= icon('arrow') ?></a>
          <a class="btn btn-ghost magnetic" href="<?= e(base_url(g($cd, 'secondary_href', 'account/register.php?role=provider'))) ?>"><span><?= e(g($cd, 'secondary_label', 'List Your Services')) ?></span><?= icon('arrow') ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var input = document.getElementById('catSearch');
  if (!input) return;
  input.addEventListener('input', function () {
    var q = this.value.toLowerCase().trim(), any = false;
    document.querySelectorAll('#accList .acc').forEach(function (acc) {
      var hit = false;
      acc.querySelectorAll('.chip').forEach(function (c) {
        var m = !q || c.textContent.toLowerCase().indexOf(q) > -1;
        c.style.display = m ? '' : 'none'; if (m) hit = true;
      });
      acc.querySelectorAll('.sub-group').forEach(function (sg) {
        var vis = Array.prototype.some.call(sg.querySelectorAll('.chip'), function (c) { return c.style.display !== 'none'; });
        sg.style.display = vis ? '' : 'none';
      });
      if (!hit && acc.querySelector('h3').textContent.toLowerCase().indexOf(q) > -1) hit = true;
      acc.style.display = hit ? '' : 'none';
      if (q && hit) acc.classList.add('open');
      if (hit) any = true;
    });
    document.getElementById('catEmpty').style.display = any ? 'none' : 'block';
  });
});
</script>
<?php render_footer(); ?>
