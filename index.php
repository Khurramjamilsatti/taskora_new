<?php
require __DIR__ . '/includes/bootstrap.php';

$hero = section('hero');           $statsBar = section('stats_bar');   $company = section('company');
$flow = section('flow');           $svcHead = section('services');     $ms = section('market_store');
$know = section('knowledge');      $calc = section('calculator');      $tools = section('tools');
$dash = section('dashboard');      $rev = section('reviews');          $career = section('career');
$sol = section('solutions');       $mkt = section('marketplace');      $trust = section('trust');
$cust = section('customers');      $bm = section('business_model');    $pro = section('professional');
$vision = section('vision');       $cta = section('cta');

$categories   = rows('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, id');
$testimonials = rows('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id');

render_head('', '', ['body' => 'has-hero home']);
render_header();

function stars($n, $cls = '')
{
    $h = '';
    for ($i = 1; $i <= 5; $i++) $h .= str_replace('class="ico ', 'class="ico ' . ($i > $n ? 'off ' : ''), icon('star', $cls));
    return $h;
}
?>
<main id="top">

<?php if (on($hero)): $hd = $hero['data']; ?>
<!-- ================= HERO ================= -->
<section class="hero grain" id="hero">
  <div class="orb orb-green" data-parallax="0.15"></div>
  <div class="orb orb-gold" data-parallax="-0.1"></div>
  <div class="container hero-grid">
    <div class="hero-copy">
      <div class="hero-tag" data-reveal><span class="dot"><?= icon('shield') ?></span><?= e($hero['tag']) ?></div>
      <h1 class="hero-title split-words"><?= emph($hero['title'], $hero['emphasis']) ?></h1>
      <p class="hero-lede" data-reveal data-delay="4"><?= e($hero['description']) ?></p>
      <div class="hero-ctas" data-reveal data-delay="5">
        <?php foreach (g($hd, 'ctas') as $c): ?>
          <a class="btn <?= $c['variant'] === 'gold' ? 'btn-gold' : 'btn-ghost' ?> magnetic" href="<?= e(strpos($c['href'], '#') === 0 ? $c['href'] : base_url($c['href'])) ?>"><span><?= e($c['label']) ?></span><?= icon('arrow') ?></a>
        <?php endforeach; ?>
      </div>
      <div class="hero-stats" data-stagger>
        <?php foreach (g($hd, 'stats') as $s): ?>
          <div><strong data-count="<?= e($s['value']) ?>"><?= e($s['value']) ?></strong><span><?= e($s['label']) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="phone-stage" data-reveal="scale" data-delay="3" aria-hidden="true">
      <div class="phone-ring r2"></div>
      <div class="phone-ring"></div>
      <div class="phone">
        <div class="phone-screen">
          <div class="phone-notch"></div>
          <div class="ps-top">
            <div class="ps-hello">Assalam-o-Alaikum, Ayesha 👋</div>
            <div class="ps-q">What can we take care of today?</div>
            <div class="ps-search"><?= icon('search') ?> Search 100+ services…</div>
          </div>
          <div class="ps-body">
            <div class="ps-chips"><?php foreach (g($hd, 'phone_categories') as $pc): ?><span><?= e($pc) ?></span><?php endforeach; ?></div>
            <div class="ps-label">Popular services <b>See all</b></div>
            <div>
              <span style="display:none"></span>
              <?php $pi = ['sun', 'bolt', 'refresh']; foreach (g($hd, 'popular_services') as $k => $p): ?>
              <div class="ps-item">
                <span class="ps-ic"><?= icon($pi[$k % 3]) ?></span>
                <div><strong><?= e($p['name']) ?></strong><small><?= e($p['price'] ?? 'Verified pros') ?></small></div>
                <span class="ps-rate"><?= icon('star') ?><?= e($p['rating']) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="ps-book">Book in 60 seconds →</div>
          </div>
        </div>
      </div>
      <div class="float-card fc-1"><span class="fc-ic"><?= icon('shield') ?></span><div><strong>Verified pro</strong><small>CNIC &amp; skills checked</small></div></div>
      <div class="float-card fc-2"><span class="fc-ic gold"><?= icon('star') ?></span><div><strong>4.9 rating</strong><small>612 reviews</small></div></div>
      <div class="float-card fc-3"><div><strong><span class="fc-live"></span>Pro en route</strong><small>Arriving in 18 min</small></div></div>
    </div>
  </div>

  <div class="scroll-cue">SCROLL</div>

  <?php if (g($hd, 'ticker')): ?>
  <div class="ticker" aria-hidden="true">
    <div class="ticker-track">
      <?php for ($r = 0; $r < 2; $r++) foreach ($hd['ticker'] as $t): ?>
        <span class="ticker-item"><?= e($t) ?> <?= icon('spark') ?></span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if (on($statsBar)): ?>
<!-- ================= STATS ================= -->
<section class="stats-band">
  <div class="container stats-grid">
    <?php foreach (g($statsBar['data'], 'items') as $s): ?>
      <div class="stat"><strong data-count="<?= e($s['value']) ?>"><?= e($s['value']) ?></strong><span><?= e($s['label']) ?></span></div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (on($company)): $cd = $company['data']; ?>
<!-- ================= COMPANY ================= -->
<section class="section" id="company">
  <div class="container company-grid">
    <div>
      <?php section_head($company, ['num' => '01']); ?>
      <ul class="why-list" data-stagger>
        <?php foreach (g($cd, 'why_items') as $w): ?>
          <li><span class="why-check"><?= icon('check') ?></span><span><?= e($w) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="company-visual" data-reveal="scale">
      <div class="cv-circle"></div>
      <div class="cv-ring"></div>
      <div class="cv-center"><div><strong data-count="<?= e(g($cd, 'badge_value', '16')) ?>"><?= e(g($cd, 'badge_value', '16')) ?></strong><span><?= e(g($cd, 'badge_label', '')) ?></span></div></div>
      <div class="cv-orbit">
        <span class="cv-chip"><?= icon('home') ?></span>
        <span class="cv-chip"><?= icon('briefcase') ?></span>
        <span class="cv-chip"><?= icon('star') ?></span>
        <span class="cv-chip"><?= icon('build') ?></span>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($flow)): ?>
<!-- ================= FLOW ================= -->
<section class="section dark flow grain">
  <div class="orb orb-gold" style="width:380px;height:380px;right:-120px;top:-100px"></div>
  <div class="container" style="position:relative">
    <?php section_head($flow, ['align' => 'center', 'light' => true, 'num' => '02']); ?>
    <div class="flow-track">
      <div class="flow-line"><span></span></div>
      <?php foreach (g($flow['data'], 'steps') as $i => $s): ?>
        <div class="flow-step" style="--i:<?= $i ?>">
          <div class="flow-dot"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
          <h4><?= e($s['title']) ?></h4>
          <?php if (!empty($s['description'])): ?><p><?= e($s['description']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($svcHead)): ?>
<!-- ================= SERVICES ================= -->
<section class="section" id="services">
  <div class="container">
    <div class="svc-toolbar">
      <?php section_head($svcHead, ['num' => '03']); ?>
      <div class="svc-search" data-reveal><?= icon('search') ?><input type="search" id="svcSearch" placeholder="Search a service, e.g. plumbing…" aria-label="Search services"></div>
    </div>
    <div class="svc-grid" data-stagger>
      <?php foreach ($categories as $c): ?>
        <a class="svc-card" href="<?= e(base_url('service.php?slug=' . $c['slug'])) ?>">
          <span class="svc-code"><?= e($c['code']) ?> — <?= e(strtoupper($c['title'])) ?></span>
          <span class="svc-ic"><?= icon($c['icon'] ?: category_icon($c['title'])) ?></span>
          <h3><?= e($c['title']) ?></h3>
          <ul><?php foreach (array_slice(lines($c['items']), 0, 5) as $it): ?><li><?= e($it) ?></li><?php endforeach; ?></ul>
          <span class="svc-more">Explore &amp; book <?= icon('arrow') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="svc-empty">No services match your search. <a class="link-arrow" href="<?= e(base_url('catalogue.php')) ?>">Browse the full catalogue <?= icon('arrow') ?></a></div>
    <?php if (g($svcHead['data'], 'disclaimer', '')): ?>
      <div class="disclaimer" data-reveal><?= icon('shield') ?><span><?= e($svcHead['data']['disclaimer']) ?></span></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if (on($ms)): $md = $ms['data']; ?>
<!-- ================= MARKET STORE ================= -->
<section class="section paper2" id="market-store">
  <div class="container">
    <div class="ms-top">
      <div>
        <?php section_head($ms, ['num' => '04']); ?>
        <a class="btn btn-dark magnetic" href="<?= e(base_url('contact.php?topic=market-store')) ?>" data-reveal><span>Partner with the store</span><?= icon('arrow') ?></a>
      </div>
      <div class="price-card" data-reveal="right">
        <div class="price-head">
          <div><span class="eyebrow eyebrow-gold"><?= e(g($md, 'pricing_tag', '')) ?></span><h3><?= e(g($md, 'pricing_title', '')) ?></h3></div>
          <span class="live-badge"><span class="fc-live"></span>Live prices</span>
        </div>
        <table class="price-table">
          <thead><tr><?php foreach (g($md, 'headers') as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr></thead>
          <tbody>
            <?php foreach (g($md, 'rows') as $r): ?>
              <tr><td><strong><?= e($r['product']) ?></strong></td><td class="strike"><?= e($r['market']) ?></td><td><?= e($r['wholesale']) ?></td><td class="partner"><?= e($r['partner']) ?></td><td><span class="save-pill"><?= e($r['save']) ?></span></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="price-note"><?= e(g($md, 'pricing_note', '')) ?></div>
      </div>
    </div>

    <div class="aud-grid" data-stagger>
      <?php foreach (g($md, 'audiences') as $i => $a): ?>
        <div class="aud-card">
          <span class="aud-num">0<?= $i + 1 ?></span>
          <span class="eyebrow"><?= e($a['tag']) ?></span>
          <h3><?= e($a['title']) ?></h3>
          <p><?= e($a['description']) ?></p>
          <ul class="check-list"><?php foreach (array_map('trim', explode(',', $a['items'])) as $it): if ($it === '') continue; ?><li><?= icon('check-circle') ?><?= e($it) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="ms-cats">
      <h3 class="split-words">One store, <em>every</em> trade.</h3>
      <div class="chip-cloud" data-stagger>
        <?php foreach (g($md, 'categories') as $c): ?><span class="chip"><?= e($c) ?></span><?php endforeach; ?>
      </div>
    </div>

    <div style="margin-top:80px">
      <span class="eyebrow" data-reveal><?= e(g($md, 'how_tag', '')) ?></span>
      <h3 class="sub-title split-words"><?= e(g($md, 'how_title', '')) ?></h3>
      <div class="how-steps" data-stagger>
        <?php foreach (g($md, 'how_steps') as $s): ?>
          <div class="how-step"><h4><?= e($s['title']) ?></h4><p><?= e($s['description']) ?></p></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($know)): ?>
<!-- ================= KNOWLEDGE ================= -->
<section class="section darker grain" id="knowledge">
  <div class="orb orb-green" style="width:420px;height:420px;left:-140px;bottom:-140px;opacity:.35"></div>
  <div class="container know-grid" style="position:relative">
    <div><?php section_head($know, ['light' => true, 'num' => '05']); ?></div>
    <ul class="guide-list" data-stagger>
      <?php foreach (g($know['data'], 'guides') as $gd): ?>
        <li><a href="<?= e(base_url('contact.php?topic=' . urlencode($gd))) ?>"><?= e($gd) ?><?= icon('arrow') ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if (on($calc)): $cd = $calc['data']; ?>
<!-- ================= CALCULATOR ================= -->
<section class="section" id="estimator">
  <div class="container">
    <div class="calc-wrap">
      <div>
        <?php section_head($calc, ['num' => '06']); ?>
        <ul class="check-list" data-stagger>
          <li><?= icon('check-circle') ?>Indicative range in seconds</li>
          <li><?= icon('check-circle') ?>Weekly &amp; monthly plan savings</li>
          <li><?= icon('check-circle') ?>Final quote confirmed before work starts</li>
        </ul>
      </div>
      <form class="calc-card" id="calcForm" data-reveal="right">
        <?= csrf_field() ?>
        <div class="field">
          <label for="calcService">Service type</label>
          <select class="input" id="calcService" name="service">
            <?php foreach (g($cd, 'service_types') as $s): ?><option value="<?= e($s['id']) ?>"><?= e($s['label']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="calcSize">Property size (<?= e(g($cd, 'size_unit', 'Marla')) ?>)</label>
          <div class="range-row">
            <input type="range" id="calcSize" name="size" min="<?= (int)g($cd, 'size_min', 3) ?>" max="<?= (int)g($cd, 'size_max', 20) ?>" value="<?= (int)g($cd, 'size_default', 5) ?>" data-unit="<?= e(g($cd, 'size_unit', 'Marla')) ?>">
            <span class="range-val" id="sizeVal"></span>
          </div>
        </div>
        <div class="field">
          <span class="label">Frequency</span>
          <div class="seg">
            <?php foreach (g($cd, 'frequencies') as $i => $f): ?>
              <label><input type="radio" name="frequency" value="<?= e($f['id']) ?>"<?= $i === 0 ? ' checked' : '' ?>><span><?= e($f['label']) ?></span></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="calc-result">
          <small>Estimated cost</small>
          <div class="calc-amount" id="calcAmount"><span class="lo">Rs 0</span> – <span class="hi">Rs 0</span></div>
          <div class="calc-meta" id="calcMeta">Calculating…</div>
        </div>
        <p class="calc-note"><?= e(g($cd, 'note', '')) ?></p>
        <a class="btn btn-gold btn-block magnetic" href="<?= e(base_url('forms.php?type=booking')) ?>"><span>Book this service</span><?= icon('arrow') ?></a>
      </form>
    </div>

    <?php if (on($tools)): ?>
    <div class="tools-row">
      <span class="eyebrow" data-reveal><?= e($tools['tag']) ?></span>
      <h3 class="sub-title split-words"><?= emph($tools['title'], $tools['emphasis']) ?></h3>
      <div class="tools-grid" data-stagger>
        <?php $ti = ['build', 'layers', 'sun', 'chart', 'calendar', 'doc', 'clock', 'truck']; foreach (g($tools['data'], 'items') as $i => $t): ?>
          <div class="tool"><span class="t-ic"><?= icon($ti[$i % 8]) ?></span><?= e($t) ?></div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if (on($dash)): $dd = $dash['data']; ?>
<!-- ================= DASHBOARD PREVIEW ================= -->
<section class="section dark grain">
  <div class="orb orb-green" style="width:500px;height:500px;right:-160px;top:20%;opacity:.4"></div>
  <div class="container dash-grid" style="position:relative">
    <div>
      <?php section_head($dash, ['light' => true, 'num' => '07']); ?>
      <a class="btn btn-gold magnetic" href="<?= e(base_url('account/register.php')) ?>" data-reveal><span>Create your free account</span><?= icon('arrow') ?></a>
    </div>
    <div class="mock-dash" data-reveal="right" aria-hidden="true">
      <div class="md-side">
        <div class="md-user"><span class="md-ava"><?= e(strtoupper(substr(g($dd, 'user_name', 'A'), 0, 1))) ?>K</span><div><strong><?= e(g($dd, 'user_name', '')) ?></strong><small><?= e(g($dd, 'user_email', '')) ?></small></div></div>
        <div class="md-nav"><?php foreach (g($dd, 'nav') as $n): ?><span><?= e($n) ?></span><?php endforeach; ?></div>
      </div>
      <div class="md-main">
        <div class="md-cards"><?php foreach (g($dd, 'cards') as $c): ?><div class="md-card"><small><?= e($c['label']) ?></small><strong><?= e($c['value']) ?></strong></div><?php endforeach; ?></div>
        <?php foreach (g($dd, 'bookings') as $i => $b): ?>
          <div class="md-book"><span class="ps-ic"><?= icon($i ? 'sun' : 'bolt') ?></span><div><strong><?= e($b['who']) ?></strong><small><?= e($b['what']) ?></small></div><span class="md-status<?= $i ? '' : ' gold' ?>"><?= e($b['status']) ?></span></div>
        <?php endforeach; ?>
        <div class="md-rate"><div><strong><?= e(g($dd, 'rate_title', '')) ?></strong><small><?= e(g($dd, 'rate_subtitle', '')) ?></small></div><div class="md-stars"><?= str_repeat(icon('star'), 5) ?></div></div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($rev)): $rd = $rev['data']; ?>
<!-- ================= REVIEWS ================= -->
<section class="section" id="reviews">
  <div class="container">
    <?php section_head($rev, ['num' => '08']); ?>
    <div class="rev-grid">
      <div class="score-card" data-reveal="left">
        <div class="orb orb-gold"></div>
        <div class="score-big" data-count="<?= e(g($rd, 'score', '4.8')) ?>"><?= e(g($rd, 'score', '4.8')) ?></div>
        <div class="score-stars"><?= str_repeat(icon('star'), 5) ?></div>
        <div class="score-count"><?= e(g($rd, 'count', '')) ?></div>
        <div class="dist">
          <?php foreach (g($rd, 'distribution') as $d): ?>
            <div class="dist-row"><span><?= (int)$d['stars'] ?>★</span><div class="dist-bar"><span style="--w:<?= (int)$d['percent'] ?>%"></span></div><span><?= (int)$d['percent'] ?>%</span></div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="t-slider" data-reveal="right">
        <div class="t-viewport">
          <div class="t-track">
            <?php foreach ($testimonials as $t): ?>
              <div class="t-slide">
                <span class="t-quote">“</span>
                <div class="t-stars"><?= stars((int)$t['stars']) ?></div>
                <p class="t-text"><?= e($t['text']) ?></p>
                <div class="t-author"><span class="t-ava"><?= e(strtoupper(substr($t['location'] ?: $t['author'], 0, 1))) ?></span><div><strong><?= e($t['author']) ?></strong><small><?= e($t['location']) ?></small></div></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="t-controls">
          <div class="t-dots"></div>
          <div class="t-arrows"><button class="prev" aria-label="Previous"><?= icon('arrow') ?></button><button class="next" aria-label="Next"><?= icon('arrow') ?></button></div>
        </div>
        <?php if (g($rd, 'note', '')): ?><p class="small-note"><?= e($rd['note']) ?></p><?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($career)): ?>
<!-- ================= CAREER ================= -->
<section class="section darker grain">
  <div class="container" style="position:relative">
    <?php section_head($career, ['light' => true, 'num' => '09']); ?>
    <div class="career-steps" data-stagger>
      <?php foreach (g($career['data'], 'steps') as $i => $s): ?>
        <div class="c-step"><b><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></b><h4><?= e($s['title']) ?></h4><p><?= e($s['description']) ?></p></div>
      <?php endforeach; ?>
    </div>
    <div class="ladder" aria-label="Career levels">
      <?php foreach (g($career['data'], 'levels') as $i => $l): ?>
        <div class="rung rung-<?= $i % 7 ?>" style="--i:<?= $i ?>"><span><?= e($l) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($sol)): $items = g($sol['data'], 'items'); ?>
<!-- ================= SOLUTIONS ================= -->
<section class="section paper2" id="business">
  <div class="container">
    <?php section_head($sol, ['num' => '10']); ?>
    <div class="sol-tabs" role="tablist" data-reveal>
      <?php foreach ($items as $i => $it): ?><button class="sol-tab<?= $i ? '' : ' active' ?>" data-tab="<?= $i ?>" role="tab" aria-selected="<?= $i ? 'false' : 'true' ?>"><?= e($it['tag']) ?></button><?php endforeach; ?>
    </div>
    <div class="sol-panels" data-reveal>
      <?php $si = ['briefcase', 'home', 'building']; foreach ($items as $i => $it): ?>
        <div class="sol-panel<?= $i ? '' : ' active' ?>" data-panel="<?= $i ?>" role="tabpanel">
          <div class="sol-text">
            <span class="eyebrow"><?= e($it['tag']) ?></span>
            <h3><?= e($it['title']) ?></h3>
            <p><?= e($it['description']) ?></p>
            <ul class="check-list"><?php foreach (array_map('trim', explode(',', $it['features'])) as $f): if ($f === '') continue; ?><li><?= icon('check-circle') ?><?= e($f) ?></li><?php endforeach; ?></ul>
            <a class="btn btn-dark magnetic" style="margin-top:28px" href="<?= e(base_url('forms.php?type=company')) ?>"><span>Register your organisation</span><?= icon('arrow') ?></a>
          </div>
          <div class="sol-visual"><div class="rings"></div><div class="big-ic"><?= icon($si[$i % 3]) ?></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($mkt)): ?>
<!-- ================= COMMUNITY MARKETPLACE ================= -->
<section class="section-sm" style="overflow:hidden">
  <div class="container"><?php section_head($mkt, ['align' => 'center', 'num' => '11']); ?></div>
  <div class="mq"><div class="mq-track">
    <?php for ($r = 0; $r < 2; $r++) foreach (g($mkt['data'], 'tags') as $t): ?><span class="chip"><?= icon('spark') ?><?= e($t) ?></span><?php endforeach; ?>
  </div></div>
</section>
<?php endif; ?>

<?php if (on($trust)): ?>
<!-- ================= TRUST ================= -->
<section class="section dark grain" id="trust">
  <div class="container trust-grid" style="position:relative">
    <div class="trust-shield" aria-hidden="true">
      <svg class="shield-big" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width=".7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 4.5 3.4 8.1 8 9 4.6-.9 8-4.5 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>
    </div>
    <div>
      <?php section_head($trust, ['light' => true, 'num' => '12']); ?>
      <ul class="trust-checks" data-stagger>
        <?php foreach (g($trust['data'], 'checks') as $c): ?><li><?= icon('check-circle') ?><?= e($c) ?></li><?php endforeach; ?>
      </ul>
      <div class="badges" data-stagger><?php foreach (g($trust['data'], 'badges') as $b): ?><span class="badge"><?= icon('lock') ?><?= e($b) ?></span><?php endforeach; ?></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($cust)): $ct = g($cust['data'], 'tags'); $half = (int)ceil(count($ct) / 2); ?>
<!-- ================= WHO WE SERVE ================= -->
<section class="section-sm" style="overflow:hidden">
  <div class="container"><?php section_head($cust, ['align' => 'center', 'num' => '13']); ?></div>
  <div class="mq"><div class="mq-track"><?php for ($r = 0; $r < 3; $r++) foreach (array_slice($ct, 0, $half) as $t): ?><span class="chip"><?= icon('users') ?><?= e($t) ?></span><?php endforeach; ?></div></div>
  <div class="mq reverse"><div class="mq-track"><?php for ($r = 0; $r < 3; $r++) foreach (array_slice($ct, $half) as $t): ?><span class="chip"><?= icon('building') ?><?= e($t) ?></span><?php endforeach; ?></div></div>
</section>
<?php endif; ?>

<?php if (on($bm)): $streams = g($bm['data'], 'streams'); ?>
<!-- ================= BUSINESS MODEL ================= -->
<section class="section paper2">
  <div class="container bm-grid">
    <div class="donut-wrap" data-reveal="scale">
      <svg class="donut" viewBox="0 0 200 200" role="img" aria-label="Revenue split chart">
        <circle cx="100" cy="100" r="80" fill="none" stroke="#0b0d0c0d" stroke-width="26"/>
        <?php foreach ($streams as $i => $s): ?>
          <circle class="seg-c" cx="100" cy="100" r="80" stroke="<?= e($s['color']) ?>" data-pct="<?= (float)$s['percent'] ?>" data-label="<?= e($s['label']) ?>" data-seg="<?= $i ?>" style="stroke-dasharray:0 503"/>
        <?php endforeach; ?>
      </svg>
      <div class="donut-center"><div><strong id="donutPct" data-def="<?= count($streams) ?>"><?= count($streams) ?></strong><span id="donutLbl" data-def="diversified revenue streams">diversified revenue streams</span></div></div>
    </div>
    <div>
      <?php section_head($bm, ['num' => '14']); ?>
      <ul class="legend" data-stagger>
        <?php foreach ($streams as $i => $s): ?><li data-seg="<?= $i ?>"><i style="background:<?= e($s['color']) ?>"></i><?= e($s['label']) ?><b><?= (float)$s['percent'] ?>%</b></li><?php endforeach; ?>
      </ul>
      <div class="bm-notes"><?php foreach (g($bm['data'], 'notes') as $n): ?><p>* <?= e($n) ?></p><?php endforeach; ?></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($pro)): $pd = $pro['data']; ?>
<!-- ================= BECOME A PRO ================= -->
<section class="section" id="professional">
  <div class="container">
    <div class="pro-band grain" data-reveal="scale">
      <div class="orb orb-gold"></div>
      <div style="position:relative">
        <?php section_head($pro, ['light' => true, 'num' => '15']); ?>
        <a class="btn btn-gold magnetic" href="<?= e(base_url(g($pd, 'cta_href', 'account/register.php?role=provider'))) ?>"><span><?= e(g($pd, 'cta_label', 'Apply to Join')) ?></span><?= icon('arrow') ?></a>
      </div>
      <div class="pro-benefits" data-stagger>
        <?php $bi = ['users', 'wallet', 'user', 'award', 'book', 'clock']; foreach (g($pd, 'benefits') as $i => $b): ?>
          <div class="benefit"><span class="b-ic"><?= icon($bi[$i % 6]) ?></span><?= e($b) ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (on($vision)): ?>
<!-- ================= VISION ================= -->
<section class="section darker grain vision">
  <div class="container" style="position:relative">
    <span class="eyebrow" data-reveal><?= e($vision['tag']) ?></span>
    <p class="vision-text"><?= e($vision['title']) ?></p>
    <div class="vision-sub" data-reveal><?= e($vision['description']) ?></div>
    <div class="vision-legal" data-reveal><?= e(g($vision['data'], 'legal', '')) ?></div>
  </div>
</section>
<?php endif; ?>

<?php if (on($cta)): ?>
<!-- ================= DOWNLOAD CTA ================= -->
<section class="section" id="download">
  <div class="container">
    <div class="cta-band grain" data-reveal="scale">
      <div class="orb orb-green"></div><div class="orb orb-gold"></div>
      <div style="position:relative">
        <span class="eyebrow eyebrow-gold"><?= e($cta['tag']) ?></span>
        <h2 class="cta-title split-words"><?= emph($cta['title'], $cta['emphasis']) ?></h2>
        <p style="color:#ffffffb3;max-width:460px"><?= e($cta['description']) ?></p>
        <div class="cta-btns">
          <?php foreach (g($cta['data'], 'buttons') as $b):
            $href = $b['href'];
            if ($b['variant'] === 'ios' && setting('app_ios')) $href = setting('app_ios');
            if ($b['variant'] === 'android' && setting('app_android')) $href = setting('app_android');
            $href = preg_match('#^(https?:|\#)#', $href) ? $href : base_url($href);
            if ($b['variant'] === 'ios'): ?>
              <a class="store-btn magnetic" href="<?= e($href) ?>"><?= icon('apple') ?><span><small>Download on the</small><strong>App Store</strong></span></a>
            <?php elseif ($b['variant'] === 'android'): ?>
              <a class="store-btn magnetic" href="<?= e($href) ?>"><?= icon('play') ?><span><small>Get it on</small><strong>Google Play</strong></span></a>
            <?php else: ?>
              <a class="btn btn-ghost magnetic" href="<?= e($href) ?>"><span><?= e($b['label']) ?></span><?= icon('arrow') ?></a>
            <?php endif; endforeach; ?>
        </div>
      </div>
      <div class="qr" aria-hidden="true">
        <div class="qr-grid">
          <?php
          mt_srand(7);
          for ($y = 0; $y < 21; $y++) for ($x = 0; $x < 21; $x++) {
              $finder = function ($fx, $fy) use ($x, $y) {
                  $dx = $x - $fx; $dy = $y - $fy;
                  if ($dx < 0 || $dy < 0 || $dx > 6 || $dy > 6) return null;
                  return ($dx === 0 || $dy === 0 || $dx === 6 || $dy === 6 || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4));
              };
              $f = $finder(0, 0); if ($f === null) $f = $finder(14, 0); if ($f === null) $f = $finder(0, 14);
              $on = $f === null ? (mt_rand(0, 100) > 52) : $f;
              if ($x >= 8 && $x <= 12 && $y >= 8 && $y <= 12) $on = false;
              echo '<i' . ($on ? '' : ' class="o"') . '></i>';
          }
          ?>
        </div>
        <div class="qr-logo"><?= logo_mark(40) ?></div>
        <div class="qr-scan"></div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

</main>
<?php render_footer(); ?>
