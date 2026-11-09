<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/forms_config.php';

$def = form_defs()['contact'];
$topic = get('topic');
$prefill = [];
if ($u = current_user()) $prefill = ['name' => $u['name'], 'email' => $u['email'], 'phone' => $u['phone']];
if ($topic) $prefill['message'] = 'I would like to know more about: ' . $topic;
$wa = preg_replace('/\D/', '', setting('whatsapp'));

render_head('Contact Us | ' . setting('site_name'), 'Contact Taskora for bookings, corporate solutions, partnerships and support. Phone, WhatsApp and email support available.', ['body' => 'has-hero']);
render_header();
?>
<main>
<section class="page-hero grain">
  <div class="orb orb-green"></div><div class="orb orb-gold"></div>
  <div class="container">
    <div class="crumbs" data-reveal><a href="<?= e(base_url('index.php')) ?>">Home</a><?= icon('chev') ?><span>Contact</span></div>
    <span class="eyebrow eyebrow-gold" data-reveal>Get in touch</span>
    <h1 class="display split-words">Let's talk about <em>what you need.</em></h1>
    <p class="lede" data-reveal data-delay="3">Whether it is a single repair or a multi-site contract, a real person on our team will help you take the next step.</p>
  </div>
</section>

<section class="section">
  <div class="container contact-grid">
    <div class="contact-cards" data-stagger>
      <a class="c-card" href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><span class="c-ic"><?= icon('phone') ?></span><div><small>Call us</small><strong><?= e(setting('phone')) ?></strong></div></a>
      <?php if ($wa): ?><a class="c-card" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><span class="c-ic"><?= icon('whatsapp') ?></span><div><small>WhatsApp</small><strong>Chat instantly</strong></div></a><?php endif; ?>
      <a class="c-card" href="mailto:<?= e(setting('email')) ?>"><span class="c-ic"><?= icon('mail') ?></span><div><small>Email</small><strong><?= e(setting('email')) ?></strong></div></a>
      <div class="c-card"><span class="c-ic"><?= icon('pin') ?></span><div><small>Office</small><strong><?= nl(setting('address')) ?></strong></div></div>
      <div class="c-card"><span class="c-ic"><?= icon('clock') ?></span><div><small>Hours</small><strong><?= e(setting('hours')) ?></strong></div></div>
      <div class="c-card"><span class="c-ic"><?= icon('globe') ?></span><div><small>Cities we serve</small><strong><?= e(implode(' · ', cities())) ?></strong></div></div>
    </div>

    <div class="form-card" data-reveal="right" data-form-wrap>
      <div class="form-card-head"><span class="eyebrow">Contact form</span><h2><?= e($def['heading']) ?></h2><p><?= e($def['intro']) ?></p></div>
      <form class="form-body" action="<?= e(base_url('ajax/form.php')) ?>" method="post" data-ajax data-success="replace" data-loading="Sending…" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="form_type" value="contact">
        <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="grid-2"><?php foreach ($def['sections']['Message'] as $f) render_field($f, $prefill); ?></div>
        <div class="submit-row">
          <span class="submit-hint"><?= icon('lock') ?><?= e($def['hint']) ?></span>
          <button class="btn btn-gold magnetic" type="submit"><span><?= e($def['button']) ?></span><?= icon('arrow') ?></button>
        </div>
      </form>
    </div>
  </div>
  <?php if (setting('map_embed')): ?>
    <div class="container"><iframe class="map-frame" src="<?= e(setting('map_embed')) ?>" loading="lazy" title="Office location" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
  <?php endif; ?>
</section>
</main>
<?php render_footer(); ?>
