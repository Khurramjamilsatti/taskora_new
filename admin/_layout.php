<?php
/**
 * Admin CMS shell.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/pro_registration.php';

function admin_nav()
{
    ensure_pro_tables();
    $newApps = (int)val("SELECT COUNT(*) FROM provider_applications WHERE status = 'submitted'");
    $newSubs = (int)val("SELECT COUNT(*) FROM form_submissions WHERE status = 'new'");
    $newBk   = (int)val("SELECT COUNT(*) FROM bookings WHERE status = 'requested'");
    $pending = (int)val("SELECT COUNT(*) FROM users WHERE role = 'provider' AND status = 'pending'");
    return [
        'Overview' => [
            ['index.php', 'Dashboard', 'grid'],
        ],
        'Operations' => [
            ['bookings.php', 'Bookings', 'calendar', $newBk],
            ['applications.php', 'Pro Applications', 'award', $newApps],
            ['submissions.php', 'Form Submissions', 'inbox', $newSubs],
            ['users.php', 'Customers & Providers', 'users', $pending],
            ['subscribers.php', 'Subscribers', 'mail'],
        ],
        'Website Content' => [
            ['sections.php', 'Page Sections', 'layers'],
            ['categories.php', 'Service Categories', 'briefcase'],
            ['catalogue.php', 'Catalogue Groups', 'list'],
            ['testimonials.php', 'Testimonials', 'star'],
            ['pages.php', 'Pages', 'doc'],
        ],
        'System' => [
            ['settings.php', 'Settings & SEO', 'settings'],
            ['admins.php', 'Admin Users', 'lock'],
        ],
    ];
}

function admin_start($title, $subtitle = '', $actions = '')
{
    $a = current_admin();
    $cur = basename($_SERVER['SCRIPT_NAME']);
    $alias = ['section-edit.php' => 'sections.php', 'booking-view.php' => 'bookings.php', 'application-file.php' => 'applications.php'];
    if (isset($alias[$cur])) $cur = $alias[$cur];
    render_head($title . ' · Admin | ' . setting('site_name'), '', ['body' => 'db', 'css' => ['css/dashboard.css']]);
    ?>
<script>try{if(localStorage.getItem('tsk_theme')==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
<div class="db-shell">
  <aside class="db-side">
    <a class="db-brand" href="index.php"><?= logo_mark(36) ?><div><?= e(setting('site_name', 'Taskora')) ?><small>CMS Dashboard</small></div></a>
    <?php foreach (admin_nav() as $group => $links): ?>
      <div class="db-group"><?= e($group) ?></div>
      <?php foreach ($links as $l): ?>
        <a class="db-link<?= $cur === $l[0] ? ' active' : '' ?>" href="<?= e($l[0]) ?>"><?= icon($l[2]) ?><?= e($l[1]) ?><?php if (!empty($l[3])): ?><span class="count"><?= (int)$l[3] ?></span><?php endif; ?></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <div class="db-side-foot">
      <a class="db-link" href="<?= e(base_url('index.php')) ?>" target="_blank"><?= icon('globe') ?>View website</a>
      <a class="db-link" href="logout.php"><?= icon('logout') ?>Sign out</a>
      <div class="db-me"><span class="db-ava"><?= e(strtoupper(substr($a['name'], 0, 1))) ?></span><div><strong><?= e($a['name']) ?></strong><small><?= e($a['email']) ?></small></div></div>
    </div>
  </aside>
  <div class="db-overlay" id="dbOverlay"></div>
  <div class="db-main">
    <header class="db-top">
      <button class="db-icon-btn db-burger" id="dbBurger" aria-label="Open menu"><?= icon('menu') ?></button>
      <div><h1><?= e($title) ?></h1><?php if ($subtitle): ?><p><?= e($subtitle) ?></p><?php endif; ?></div>
      <div class="db-top-actions"><?= $actions ?><button class="db-icon-btn" id="themeBtn" aria-label="Toggle theme"><?= icon('moon') ?></button></div>
    </header>
    <main class="db-content">
      <?php foreach (flash() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= icon($f['type'] === 'error' ? 'x' : 'check-circle') ?><span><?= e($f['msg']) ?></span></div>
      <?php endforeach; ?>
<?php
}

function admin_end($extraJs = '')
{
    ?>
    </main>
  </div>
</div>
<div class="toast-stack" id="toasts" aria-live="polite"></div>
<div id="cursorDot" hidden></div><div id="cursorRing" hidden></div>
<script src="<?= asset('vendor/jquery-3.7.1.min.js') ?>"></script>
<script>window.TASKORA = { base: <?= json_encode(base_url('')) ?>, csrf: <?= json_encode(csrf_token()) ?> };</script>
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/dashboard.js') ?>"></script>
<script src="<?= asset('js/admin.js') ?>"></script>
<?= $extraJs ?>
</body></html>
<?php
}

/** Handle an image upload safely; returns relative path or null. */
function handle_upload($field, $prefix = 'img')
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    if ($_FILES[$field]['size'] > 3 * 1024 * 1024) { flash('error', 'Image must be smaller than 3 MB.'); return null; }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $allowed = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'gif' => 'image/gif', 'ico' => 'image/x-icon'];
    if (!isset($allowed[$ext])) { flash('error', 'Only PNG, JPG, WEBP, GIF or ICO images are allowed.'); return null; }
    if ($ext !== 'ico' && @getimagesize($_FILES[$field]['tmp_name']) === false) { flash('error', 'That file is not a valid image.'); return null; }
    if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0775, true);
    $name = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], UPLOAD_DIR . '/' . $name)) { flash('error', 'Upload failed.'); return null; }
    return 'uploads/' . $name;
}
