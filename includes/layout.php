<?php
/**
 * Front-end layout helpers: logo, <head>, header, footer.
 */

function logo_mark($size = 40)
{
    // Original geometric mark: rounded emerald tile, gold "T" crossbar + checked stem.
    return '<svg class="logo-mark" width="' . $size . '" height="' . $size . '" viewBox="0 0 48 48" aria-hidden="true">'
        . '<defs><linearGradient id="lmG" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0e8f57"/><stop offset="1" stop-color="#04382b"/></linearGradient>'
        // userSpaceOnUse: a horizontal line has a zero-height bbox, so objectBoundingBox gradients would not paint.
        . '<linearGradient id="lmA" gradientUnits="userSpaceOnUse" x1="13" y1="15.5" x2="35" y2="15.5"><stop offset="0" stop-color="#f3d77a"/><stop offset=".5" stop-color="#d4af37"/><stop offset="1" stop-color="#b8901f"/></linearGradient></defs>'
        . '<rect x="2" y="2" width="44" height="44" rx="13" fill="url(#lmG)"/>'
        . '<rect x="2.5" y="2.5" width="43" height="43" rx="12.5" fill="none" stroke="#d4af37" stroke-opacity=".45"/>'
        . '<path class="lm-bar" d="M13 15.5h22" stroke="url(#lmA)" stroke-width="4.2" stroke-linecap="round"/>'
        . '<path class="lm-stem" d="M24 15.5v15.5l-4.2-3.6" fill="none" stroke="#fff" stroke-width="4.2" stroke-linecap="round" stroke-linejoin="round"/>'
        . '<circle cx="33.5" cy="31.5" r="2.6" fill="#d4af37"/>'
        . '</svg>';
}

function site_logo($light = false)
{
    $img = setting('logo');
    if ($img) {
        return '<img src="' . e(base_url($img)) . '" alt="' . e(setting('site_name', 'Taskora')) . '" class="logo-img">';
    }
    return logo_mark() . '<span class="logo-word">' . e(setting('site_name', 'Taskora')) . '</span>';
}

function render_head($title = '', $desc = '', $opts = [])
{
    $site  = setting('site_name', 'Taskora');
    $full  = $title ? $title : setting('meta_title', $site);
    $desc  = $desc ? $desc : setting('meta_description');
    $canon = (isset($_SERVER['HTTP_HOST']) ? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : '') . strtok($_SERVER['REQUEST_URI'] ?? '/', '#');
    $fav   = setting('favicon') ? base_url(setting('favicon')) : base_url('assets/img/favicon.svg');
    $body  = $opts['body'] ?? '';
    $ga    = setting('google_analytics');
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($full) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="keywords" content="<?= e(setting('meta_keywords')) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($site) ?>">
<meta property="og:title" content="<?= e($full) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#04382b">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="<?= e($fav) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<?php if (!empty($opts['css'])) foreach ((array)$opts['css'] as $c): ?><link rel="stylesheet" href="<?= asset($c) ?>">
<?php endforeach; ?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $site,
    'url' => base_url(''), 'email' => setting('email'), 'telephone' => setting('phone'),
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => setting('address'), 'addressCountry' => 'PK'],
    'description' => setting('meta_description'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php if ($ga): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= e($ga) ?>');</script>
<?php endif; ?>
</head>
<body class="<?= e($body) ?>">
<?php
}

function render_header($opts = [])
{
    $promo = section('promo');
    $solid = !empty($opts['solid']);
    // [label, href, scroll-spy section id (home page only)]
    $links = [
        ['Market Store', 'index.php#market-store', 'market-store'],
        ['Knowledge', 'index.php#knowledge', 'knowledge'],
        ['Business', 'index.php#business', 'business'],
        ['Professionals', 'index.php#professional', 'professional'],
        ['Catalogue', 'catalogue.php', ''],
        ['Contact', 'contact.php', ''],
    ];
    $cats = rows('SELECT title, slug, icon, items FROM categories WHERE is_active = 1 ORDER BY sort_order, id');
    $user = current_user();
    $acct = $user ? ['account/index.php', 'My account'] : ['account/login.php', 'Sign in / Join'];
    $wa = preg_replace('/\D/', '', setting('whatsapp'));
    $tel = preg_replace('/\s+/', '', setting('phone'));
    $socials = ['facebook' => 'facebook', 'instagram' => 'instagram', 'linkedin' => 'linkedin', 'twitter' => 'x-social', 'youtube' => 'youtube'];
    ?>
<div class="preloader" id="preloader" aria-hidden="true">
  <div class="pl-inner">
    <?= logo_mark(64) ?>
    <div class="pl-word"><?= e(setting('site_name', 'Taskora')) ?></div>
    <div class="pl-bar"><span></span></div>
  </div>
</div>
<div class="scroll-progress" id="scrollProgress"></div>
<div class="cursor-dot" id="cursorDot"></div><div class="cursor-ring" id="cursorRing"></div>

<?php if (on($promo)): ?>
<div class="promo" id="promo">
  <div class="promo-track">
    <span class="promo-spark"><?= icon('spark') ?></span>
    <span class="promo-text"><?= e($promo['title']) ?></span>
    <?php if (g($promo['data'], 'link_label', '')): ?>
      <a class="promo-link" href="<?= e(base_url(g($promo['data'], 'link_href', '#'))) ?>"><?= e($promo['data']['link_label']) ?> <?= icon('arrow') ?></a>
    <?php endif; ?>
  </div>
  <button class="promo-close" aria-label="Dismiss"><?= icon('x') ?></button>
</div>
<?php endif; ?>

<header class="site-header<?= $solid ? ' is-solid' : '' ?>" id="siteHeader">
  <div class="container nav-wrap">
    <a href="<?= e(base_url('index.php')) ?>" class="logo" aria-label="Home"><?= site_logo() ?></a>
    <nav class="main-nav" id="mainNav" aria-label="Main">
      <span class="nav-glider" aria-hidden="true"></span>
      <div class="nav-item has-mega" id="megaItem">
        <a class="nav-link" href="<?= e(base_url('index.php#services')) ?>" data-spy="services" aria-haspopup="true" aria-expanded="false">Services<?= icon('chev-down', 'nav-chev') ?></a>
        <div class="mega" role="region" aria-label="Service categories">
          <div class="mega-panel">
            <div class="mega-main">
              <div class="mega-head"><span class="eyebrow">Browse <?= count($cats) ?> categories</span><a class="link-arrow" href="<?= e(base_url('catalogue.php')) ?>">Full catalogue <?= icon('arrow') ?></a></div>
              <div class="mega-grid">
                <?php foreach ($cats as $i => $c): ?>
                  <a class="mega-link" href="<?= e(base_url('service.php?slug=' . $c['slug'])) ?>" style="--i:<?= $i ?>">
                    <span class="mega-ic"><?= icon($c['icon'] ?: 'spark') ?></span>
                    <span><strong><?= e($c['title']) ?></strong><small><?= count(lines($c['items'])) ?> services</small></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
            <aside class="mega-feature">
              <div class="orb orb-gold"></div>
              <span class="eyebrow eyebrow-gold">Not sure where to start?</span>
              <h3>Tell us the job. <em>We'll match the pro.</em></h3>
              <p>Describe what you need and a verified professional nearby will send a clear quotation — usually within 15 minutes.</p>
              <a class="btn btn-gold btn-sm" href="<?= e(base_url('forms.php?type=booking')) ?>"><span>Book a service</span><?= icon('arrow') ?></a>
              <a class="mega-est" href="<?= e(base_url('index.php#estimator')) ?>"><?= icon('calc') ?>Try the cost estimator</a>
            </aside>
          </div>
        </div>
      </div>
      <?php foreach ($links as $l): ?>
        <a class="nav-link" href="<?= e(base_url($l[1])) ?>"<?= $l[2] ? ' data-spy="' . e($l[2]) . '"' : '' ?>><?= e($l[0]) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="nav-actions">
      <a class="nav-account<?= $user ? '' : ' is-guest' ?>" href="<?= e(base_url($acct[0])) ?>" title="<?= e($acct[1]) ?>"><?= icon('user') ?><span><?= e($acct[1]) ?></span></a>
      <?php if (!$user): ?><a class="nav-join" href="<?= e(base_url('account/register.php?role=provider')) ?>"><?= icon('briefcase') ?><span>Join as a Pro</span></a><?php endif; ?>
      <a class="btn btn-gold btn-sm magnetic nav-cta" href="<?= e(base_url('index.php#download')) ?>"><span>Download App</span></a>
      <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <div class="mm-bg"></div>
  <div class="mm-inner">
    <nav class="mm-nav" aria-label="Mobile">
      <div class="mm-row" style="--i:0">
        <button class="mm-link mm-toggle" aria-expanded="false"><small>01</small>Services<?= icon('chev-down', 'mm-chev') ?></button>
        <div class="mm-sub"><div><div class="mm-sub-grid">
          <?php foreach ($cats as $c): ?>
            <a href="<?= e(base_url('service.php?slug=' . $c['slug'])) ?>"><span class="mm-sub-ic"><?= icon($c['icon'] ?: 'spark') ?></span><?= e($c['title']) ?></a>
          <?php endforeach; ?>
          <a class="mm-sub-all" href="<?= e(base_url('catalogue.php')) ?>">View the full catalogue <?= icon('arrow') ?></a>
        </div></div></div>
      </div>
      <?php foreach ($links as $i => $l): ?>
        <div class="mm-row" style="--i:<?= $i + 1 ?>"><a class="mm-link" href="<?= e(base_url($l[1])) ?>"><small><?= str_pad($i + 2, 2, '0', STR_PAD_LEFT) ?></small><?= e($l[0]) ?><?= icon('arrow-ur', 'mm-arr') ?></a></div>
      <?php endforeach; ?>
    </nav>
    <div class="mm-cta">
      <a class="btn btn-gold" href="<?= e(base_url('forms.php?type=booking')) ?>"><span>Book a service</span><?= icon('arrow') ?></a>
      <a class="btn btn-ghost" href="<?= e(base_url($acct[0])) ?>"><span><?= e($acct[1]) ?></span></a>
      <?php if (!$user): ?><a class="btn btn-ghost mm-join" href="<?= e(base_url('account/register.php?role=provider')) ?>"><?= icon('briefcase') ?><span>Join as a Professional</span></a><?php endif; ?>
    </div>
    <div class="mm-contact">
      <a href="tel:<?= e($tel) ?>"><span class="mm-c-ic"><?= icon('phone') ?></span><span><small>Call us</small><?= e(setting('phone')) ?></span></a>
      <?php if ($wa): ?><a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><span class="mm-c-ic"><?= icon('whatsapp') ?></span><span><small>WhatsApp</small>Chat instantly</span></a><?php endif; ?>
      <a href="mailto:<?= e(setting('email')) ?>"><span class="mm-c-ic"><?= icon('mail') ?></span><span><small>Email</small><?= e(setting('email')) ?></span></a>
    </div>
    <div class="mm-social">
      <?php foreach ($socials as $k => $ic): if (!setting($k)) continue; ?><a href="<?= e(setting($k)) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($k)) ?>"><?= icon($ic) ?></a><?php endforeach; ?>
    </div>
  </div>
</div>
<?php
}

function render_footer($opts = [])
{
    $cats = rows('SELECT title, slug FROM categories WHERE is_active = 1 ORDER BY sort_order LIMIT 6');
    $year = date('Y');
    $wa = preg_replace('/\D/', '', setting('whatsapp'));
    $socials = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'twitter' => 'X', 'youtube' => 'YouTube'];
    ?>
<footer class="site-footer">
  <div class="footer-glow"></div>
  <div class="container">
    <div class="footer-news" data-reveal>
      <div>
        <span class="eyebrow eyebrow-gold">Stay in the loop</span>
        <h3 class="footer-news-title">Seasonal maintenance tips &amp; <em>member-only offers.</em></h3>
      </div>
      <form class="news-form" id="newsForm" novalidate>
        <?= csrf_field() ?>
        <input type="email" name="email" placeholder="Your email address" required aria-label="Email address">
        <button class="btn btn-gold magnetic" type="submit"><span>Subscribe</span><?= icon('arrow') ?></button>
      </form>
    </div>

    <div class="footer-grid">
      <div class="footer-brand">
        <a href="<?= e(base_url('index.php')) ?>" class="logo logo-light"><?= site_logo(true) ?></a>
        <p><?= e(setting('footer_about')) ?></p>
        <ul class="footer-contact">
          <li><?= icon('phone') ?><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
          <li><?= icon('mail') ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
          <li><?= icon('pin') ?><span><?= e(setting('address')) ?></span></li>
        </ul>
        <div class="socials">
          <?php foreach ($socials as $k => $label): if (!setting($k)) continue; ?>
            <a href="<?= e(setting($k)) ?>" aria-label="<?= e($label) ?>" target="_blank" rel="noopener"><?= icon($k === 'twitter' ? 'x-social' : $k) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="footer-col">
        <h4>Services</h4>
        <?php foreach ($cats as $c): ?><a href="<?= e(base_url('service.php?slug=' . $c['slug'])) ?>"><?= e($c['title']) ?></a><?php endforeach; ?>
        <a href="<?= e(base_url('catalogue.php')) ?>" class="gold-link">Full catalogue →</a>
      </div>
      <div class="footer-col">
        <h4>Solutions</h4>
        <a href="<?= e(base_url('index.php#business')) ?>">Business</a>
        <a href="<?= e(base_url('index.php#business')) ?>">Housing Societies</a>
        <a href="<?= e(base_url('index.php#business')) ?>">Enterprise</a>
        <a href="<?= e(base_url('forms.php?type=company')) ?>">Company Registration</a>
        <a href="<?= e(base_url('index.php#market-store')) ?>">Market Store</a>
      </div>
      <div class="footer-col">
        <h4>Company</h4>
        <a href="<?= e(base_url('page.php?slug=about')) ?>">About Us</a>
        <a href="<?= e(base_url('account/register.php?role=provider')) ?>">Become a Professional</a>
        <a href="<?= e(base_url('index.php#trust')) ?>">Trust &amp; Safety</a>
        <a href="<?= e(base_url('contact.php')) ?>">Contact</a>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <a href="<?= e(base_url('forms.php?type=booking')) ?>">Book a Service</a>
        <a href="<?= e(base_url('forms.php?type=feedback')) ?>">Feedback</a>
        <a href="<?= e(base_url('forms.php?type=complaint')) ?>">Complaints</a>
        <a href="<?= e(base_url('forms.php?type=refund')) ?>">Refund Request</a>
        <a href="<?= e(base_url('forms.php?type=insurance')) ?>">Insurance Claim</a>
      </div>
    </div>

    <div class="footer-bottom">
      <span><?= e(str_replace('{year}', $year, setting('copyright'))) ?></span>
      <span class="footer-legal">
        <a href="<?= e(base_url('page.php?slug=privacy')) ?>">Privacy</a>
        <a href="<?= e(base_url('page.php?slug=terms')) ?>">Terms</a>
        <a href="<?= e(base_url('sitemap.php')) ?>">Sitemap</a>
      </span>
      <span class="powered"><?= e(setting('powered_by')) ?></span>
    </div>
  </div>
  <div class="footer-giant" aria-hidden="true"><?= e(setting('site_name', 'Taskora')) ?></div>
</footer>

<?php if ($wa): ?>
<a class="wa-float" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><?= icon('whatsapp') ?><span>Chat with us</span></a>
<?php endif; ?>
<button class="to-top" id="toTop" aria-label="Back to top">
  <svg viewBox="0 0 44 44"><circle cx="22" cy="22" r="20" class="tt-track"/><circle cx="22" cy="22" r="20" class="tt-bar" id="toTopBar"/></svg>
  <?= icon('arrow-up') ?>
</button>
<div class="toast-stack" id="toasts" aria-live="polite"></div>

<script src="<?= asset('vendor/jquery-3.7.1.min.js') ?>"></script>
<script>window.TASKORA = { base: <?= json_encode(base_url('')) ?>, csrf: <?= json_encode(csrf_token()) ?> };</script>
<script src="<?= asset('js/main.js') ?>"></script>
<?php if (!empty($opts['js'])) foreach ((array)$opts['js'] as $j): ?><script src="<?= asset($j) ?>"></script>
<?php endforeach; ?>
</body>
</html>
<?php
}

/** Section heading block. */
function section_head($s, $opts = [])
{
    if (!$s) return;
    $align = $opts['align'] ?? 'left';
    $light = !empty($opts['light']);
    $num   = $opts['num'] ?? '';
    ?>
  <div class="section-head sh-<?= e($align) ?><?= $light ? ' sh-light' : '' ?>">
    <?php if ($s['tag']): ?><span class="eyebrow" data-reveal><?php if ($num): ?><b><?= e($num) ?></b><?php endif; ?><?= e($s['tag']) ?></span><?php endif; ?>
    <?php if ($s['title']): ?><h2 class="display split-words"><?= emph($s['title'], $s['emphasis']) ?></h2><?php endif; ?>
    <?php if ($s['description']): ?><p class="lede" data-reveal data-delay="2"><?= e($s['description']) ?></p><?php endif; ?>
  </div>
    <?php
}
