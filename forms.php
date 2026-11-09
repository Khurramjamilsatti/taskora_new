<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/forms_config.php';

$defs = form_defs();
$type = get('type', 'booking');
if (!isset($defs[$type]) || !empty($defs[$type]['hidden'])) $type = 'booking';
$def = $defs[$type];

$prefill = ['category' => get('category'), 'service' => get('service')];
if ($u = current_user()) {
    $prefill += ['name' => $u['name'], 'phone' => $u['phone'], 'email' => $u['email'], 'city' => $u['city']];
}
if ($prefill['service'] && !$prefill['category']) {
    foreach (rows('SELECT title, items FROM categories') as $c) {
        if (stripos($c['items'], $prefill['service']) !== false) { $prefill['category'] = $c['title']; break; }
    }
}

render_head($def['heading'] . ' | ' . setting('site_name'), $def['intro'], ['body' => 'has-hero']);
render_header();
?>
<main>
<section class="page-hero grain" style="padding-bottom:90px">
  <div class="orb orb-green"></div><div class="orb orb-gold"></div>
  <div class="container">
    <div class="crumbs" data-reveal><a href="<?= e(base_url('index.php')) ?>">Home</a><?= icon('chev') ?><span>Forms &amp; Registration</span></div>
    <span class="eyebrow eyebrow-gold" data-reveal>Forms &amp; registration</span>
    <h1 class="display split-words">Get set up <em>with Taskora.</em></h1>
    <p class="lede" data-reveal data-delay="3">Book a service, onboard your company, or share feedback and claims — all through the same verified, tracked process.</p>
  </div>
</section>

<section class="section" style="padding-top:70px">
  <div class="container form-shell">
    <nav class="form-nav" data-reveal="left" aria-label="Forms">
      <?php foreach ($defs as $k => $d): if (!empty($d['hidden'])) continue; ?>
        <a href="<?= e(base_url('forms.php?type=' . $k)) ?>" class="<?= $k === $type ? 'active' : '' ?>"><?= icon($d['icon']) ?><?= e($d['title']) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="form-card" data-reveal data-form-wrap>
      <div class="form-card-head">
        <span class="eyebrow"><?= e($def['title']) ?></span>
        <h2><?= e($def['heading']) ?></h2>
        <p><?= e($def['intro']) ?></p>
      </div>
      <form class="form-body" action="<?= e(base_url('ajax/form.php')) ?>" method="post" data-ajax data-success="replace" data-loading="Submitting…" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="form_type" value="<?= e($type) ?>">
        <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <?php foreach ($def['sections'] as $title => $fields): ?>
          <div class="fieldset">
            <h3 class="fieldset-title"><?= e($title) ?></h3>
            <div class="grid-2"><?php foreach ($fields as $f) render_field($f, $prefill); ?></div>
          </div>
        <?php endforeach; ?>
        <div class="submit-row">
          <span class="submit-hint"><?= icon('clock') ?><?= e($def['hint']) ?></span>
          <button class="btn btn-gold magnetic" type="submit"><span><?= e($def['button']) ?></span><?= icon('arrow') ?></button>
        </div>
      </form>
    </div>
  </div>
</section>
</main>
<?php render_footer(); ?>
