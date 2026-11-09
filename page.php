<?php
require __DIR__ . '/includes/bootstrap.php';

$p = row('SELECT * FROM pages WHERE slug = ? AND is_active = 1', [get('slug')]);
if (!$p) { http_response_code(404); $p = ['title' => 'Page not found', 'meta_description' => '', 'content' => '<p>Sorry, this page does not exist.</p>', 'updated_at' => date('Y-m-d')]; }

render_head($p['title'] . ' | ' . setting('site_name'), $p['meta_description'], ['body' => 'has-hero']);
render_header();
?>
<main>
<section class="page-hero grain" style="padding-bottom:90px">
  <div class="orb orb-green"></div><div class="orb orb-gold"></div>
  <div class="container">
    <div class="crumbs" data-reveal><a href="<?= e(base_url('index.php')) ?>">Home</a><?= icon('chev') ?><span><?= e($p['title']) ?></span></div>
    <h1 class="display split-words"><?= e($p['title']) ?></h1>
    <p class="lede" data-reveal data-delay="2">Last updated <?= e(date('j F Y', strtotime($p['updated_at']))) ?></p>
  </div>
</section>
<section class="section">
  <div class="container" style="max-width:860px">
    <article class="prose" data-reveal><?= $p['content'] ?></article>
  </div>
</section>
</main>
<?php render_footer(); ?>
