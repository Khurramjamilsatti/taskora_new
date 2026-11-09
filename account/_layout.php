<?php
/**
 * Customer / provider portal shell.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

function portal_nav($u)
{
    $unread = (int)val('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0', [$u['id']]);
    if ($u['role'] === 'provider') {
        $open = (int)val("SELECT COUNT(*) FROM bookings WHERE status = 'requested' AND provider_id IS NULL");
        return [
            'Workspace' => [
                ['index.php', 'Overview', 'grid'],
                ['requests.php', 'Booking Requests', 'inbox', $open],
                ['jobs.php', 'My Jobs', 'briefcase'],
            ],
            'Account' => [
                ['profile.php', 'Profile', 'user'],
                ['password.php', 'Change Password', 'lock'],
            ],
        ];
    }
    return [
        'Bookings' => [
            ['index.php', 'Overview', 'grid'],
            ['services.php', 'Book a Service', 'plus'],
            ['bookings.php', 'My Bookings', 'list'],
        ],
        'Support' => [
            ['../forms.php?type=complaint', 'Complaint', 'doc'],
            ['../forms.php?type=refund', 'Refund Request', 'wallet'],
            ['../forms.php?type=insurance', 'Insurance Claim', 'shield'],
        ],
        'Account' => [
            ['profile.php', 'Profile', 'user'],
            ['password.php', 'Change Password', 'lock'],
        ],
    ];
}

function portal_start($title, $subtitle = '')
{
    $u = current_user();
    $cur = basename($_SERVER['SCRIPT_NAME']);
    $notifs = rows('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 12', [$u['id']]);
    $unread = 0; foreach ($notifs as $n) if (!$n['is_read']) $unread++;
    render_head($title . ' | ' . setting('site_name'), '', ['body' => 'db', 'css' => ['css/dashboard.css']]);
    ?>
<script>try{if(localStorage.getItem('tsk_theme')==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
<div class="db-shell">
  <aside class="db-side" id="dbSide">
    <a class="db-brand" href="<?= e(base_url('index.php')) ?>"><?= logo_mark(36) ?><div><?= e(setting('site_name', 'Taskora')) ?><small><?= $u['role'] === 'provider' ? 'Provider' : 'Customer' ?> portal</small></div></a>
    <?php foreach (portal_nav($u) as $group => $links): ?>
      <div class="db-group"><?= e($group) ?></div>
      <?php foreach ($links as $l): ?>
        <a class="db-link<?= $cur === basename($l[0]) ? ' active' : '' ?>" href="<?= e($l[0]) ?>"><?= icon($l[2]) ?><?= e($l[1]) ?><?php if (!empty($l[3])): ?><span class="count"><?= (int)$l[3] ?></span><?php endif; ?></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <div class="db-side-foot">
      <a class="db-link" href="<?= e(base_url('index.php')) ?>"><?= icon('home') ?>Back to website</a>
      <a class="db-link" href="logout.php"><?= icon('logout') ?>Sign out</a>
      <div class="db-me"><span class="db-ava"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></span><div><strong><?= e($u['name']) ?></strong><small><?= e($u['email']) ?></small></div></div>
    </div>
  </aside>
  <div class="db-overlay" id="dbOverlay"></div>
  <div class="db-main">
    <header class="db-top">
      <button class="db-icon-btn db-burger" id="dbBurger" aria-label="Open menu"><?= icon('menu') ?></button>
      <div><h1><?= e($title) ?></h1><?php if ($subtitle): ?><p><?= e($subtitle) ?></p><?php endif; ?></div>
      <div class="db-top-actions">
        <button class="db-icon-btn" id="themeBtn" aria-label="Toggle theme"><?= icon('moon') ?></button>
        <div class="notif-wrap">
          <button class="db-icon-btn" id="notifBtn" aria-label="Notifications"><?= icon('bell') ?><?php if ($unread): ?><span class="dotn"></span><?php endif; ?></button>
          <div class="notif-menu" id="notifMenu">
            <div class="notif-head">Notifications <?php if ($unread): ?><button id="markAll">Mark all read</button><?php endif; ?></div>
            <?php if (!$notifs): ?><div class="notif-empty">No notifications yet.</div><?php endif; ?>
            <?php foreach ($notifs as $n): ?>
              <a class="notif-item<?= $n['is_read'] ? '' : ' unread' ?>" href="<?= e($n['link'] ? base_url($n['link']) : '#') ?>"><strong><?= e($n['title']) ?></strong><?= e($n['body']) ?><br><small><?= e(time_ago($n['created_at'])) ?></small></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </header>
    <main class="db-content">
      <?php foreach (flash() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= icon($f['type'] === 'error' ? 'x' : 'check-circle') ?><span><?= e($f['msg']) ?></span></div>
      <?php endforeach; ?>
<?php
}

function portal_end()
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
</body></html>
<?php
}

/** Render the status timeline for a booking. */
function booking_steps($status)
{
    $flow = ['requested' => 'Requested', 'provider_joined' => 'Provider joined', 'quoted' => 'Quotation', 'deal_locked' => 'Deal locked', 'in_progress' => 'In progress', 'completed' => 'Completed'];
    $keys = array_keys($flow);
    $idx = array_search($status, $keys, true);
    echo '<div class="steps">';
    $n = 1;
    foreach ($flow as $k => $label) {
        $i = array_search($k, $keys, true);
        $cls = $status === 'cancelled' ? '' : ($idx !== false && $i < $idx ? 'done' : ($i === $idx ? ($status === 'completed' ? 'done' : 'cur') : ''));
        echo '<div class="st ' . $cls . '"><i>' . ($cls === 'done' ? '✓' : $n) . '</i>' . e($label) . '</div>';
        $n++;
    }
    echo '</div>';
}
