<?php
require __DIR__ . '/includes/bootstrap.php';

$cat = row('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [get('slug')]);
if (!$cat) {
    http_response_code(404);
    render_head('Service not found', '', ['body' => 'has-hero']);
    render_header();
    echo '<section class="page-hero grain"><div class="container"><h1 class="display">Service not <em>found.</em></h1><p class="lede">The service you are looking for may have moved.</p><a class="btn btn-gold" style="margin-top:30px" href="' . e(base_url('index.php#services')) . '"><span>Browse all services</span>' . icon('arrow') . '</a></div></section>';
    render_footer();
    exit;
}
$others = rows('SELECT title, slug, icon, code FROM categories WHERE is_active = 1 AND id <> ? ORDER BY RAND() LIMIT 4', [$cat['id']]);
$items = lines($cat['items']);
$keyword = setting('seo_keyword');

render_head($cat['meta_title'] ?: $cat['title'] . ' | ' . setting('site_name'), $cat['meta_description'] ?: $cat['short_desc'], ['body' => 'has-hero']);
render_header();
?>
<main>
<section class="page-hero grain">
  <div class="orb orb-green"></div><div class="orb orb-gold"></div>
  <div class="container">
    <div class="crumbs" data-reveal><a href="<?= e(base_url('index.php')) ?>">Home</a><?= icon('chev') ?><a href="<?= e(base_url('index.php#services')) ?>">Services</a><?= icon('chev') ?><span><?= e($cat['title']) ?></span></div>
    <span class="eyebrow eyebrow-gold" data-reveal><?= e($cat['code']) ?> · Verified category</span>
    <h1 class="display split-words"><?= e($cat['title']) ?> <em>in Pakistan.</em></h1>
    <p class="lede" data-reveal data-delay="3"><?= e($cat['short_desc']) ?></p>
    <div class="hero-ctas" style="margin-top:36px" data-reveal data-delay="4">
      <a class="btn btn-gold magnetic" href="<?= e(base_url('forms.php?type=booking&category=' . urlencode($cat['title']))) ?>"><span>Book now</span><?= icon('arrow') ?></a>
      <a class="btn btn-ghost magnetic" href="<?= e(base_url('index.php#estimator')) ?>"><span>Estimate cost</span><?= icon('calc') ?></a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container svc-detail">
    <div>
      <span class="eyebrow" data-reveal>What's included</span>
      <h2 class="display split-words" style="font-size:clamp(2rem,4vw,3rem)">Choose your <em>service.</em></h2>
      <div class="sub-svc-grid" data-stagger>
        <?php foreach ($items as $it): ?>
          <a class="sub-svc" href="<?= e(base_url('forms.php?type=booking&category=' . urlencode($cat['title']) . '&service=' . urlencode($it))) ?>">
            <span class="s-ic"><?= icon($cat['icon'] ?: 'spark') ?></span><strong><?= e($it) ?></strong><?= icon('arrow', 'arr') ?>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="prose" data-reveal><?= $cat['description'] ?></div>

      <div style="margin-top:50px" data-reveal>
        <span class="eyebrow">How it works</span>
        <div class="how-steps" style="grid-template-columns:repeat(4,1fr)" data-stagger>
          <div class="how-step"><h4>Request</h4><p>Tell us what you need and where.</p></div>
          <div class="how-step"><h4>Quote</h4><p>A verified pro shares a clear price.</p></div>
          <div class="how-step"><h4>Track</h4><p>Follow the job live, chat any time.</p></div>
          <div class="how-step"><h4>Review</h4><p>Pay securely and rate the work.</p></div>
        </div>
      </div>
    </div>
    <aside class="sticky-card grain" data-reveal="right">
      <div class="orb orb-gold"></div>
      <h3>Need <?= e(strtolower($cat['title'])) ?> today?</h3>
      <p>Most requests are matched with a verified professional within 15 minutes during service hours.</p>
      <ul class="check-list" style="margin:18px 0 22px">
        <li><?= icon('check-circle') ?>Verified, rated professionals</li>
        <li><?= icon('check-circle') ?>Upfront quotation</li>
        <li><?= icon('check-circle') ?>Secure digital payment</li>
        <li><?= icon('check-circle') ?>Complaint resolution in 24h</li>
      </ul>
      <a class="btn btn-gold btn-block" href="<?= e(base_url('forms.php?type=booking&category=' . urlencode($cat['title']))) ?>"><span>Book a professional</span><?= icon('arrow') ?></a>
      <a class="btn btn-ghost btn-block" style="margin-top:10px" href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><span><?= e(setting('phone')) ?></span><?= icon('phone') ?></a>
    </aside>
  </div>
</section>

<?php if ($others): ?>
<section class="section paper2">
  <div class="container">
    <span class="eyebrow" data-reveal>Explore more</span>
    <h2 class="display split-words" style="font-size:clamp(2rem,4vw,3rem);margin-bottom:40px">Other <em>categories.</em></h2>
    <div class="other-cats" data-stagger>
      <?php foreach ($others as $o): ?>
        <a class="svc-card" style="min-height:0" href="<?= e(base_url('service.php?slug=' . $o['slug'])) ?>">
          <span class="svc-code"><?= e($o['code']) ?></span>
          <span class="svc-ic"><?= icon($o['icon'] ?: 'spark') ?></span>
          <h3><?= e($o['title']) ?></h3>
          <span class="svc-more">Explore <?= icon('arrow') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'Service', 'name' => $cat['title'],
    'description' => $cat['short_desc'], 'areaServed' => cities(),
    'provider' => ['@type' => 'Organization', 'name' => setting('site_name')],
    'hasOfferCatalog' => ['@type' => 'OfferCatalog', 'name' => $cat['title'], 'itemListElement' => array_map(function ($i) { return ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $i]]; }, $items)],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php render_footer(); ?>
