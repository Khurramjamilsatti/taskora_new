<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if (current_admin()) redirect('admin/index.php');

$error = '';
if (is_post()) {
    csrf_check();
    if (!throttle('admin_login', 6, 600)) {
        $error = 'Too many attempts. Please wait 10 minutes.';
    } else {
        $a = row('SELECT * FROM admins WHERE email = ?', [strtolower(post('email'))]);
        if ($a && password_verify(post('password'), $a['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $a['id'];
            q('UPDATE admins SET last_login = NOW() WHERE id = ?', [$a['id']]);
            redirect('admin/index.php');
        }
        $error = 'Invalid email or password.';
    }
}
$flashes = flash();
render_head('Admin sign in | ' . setting('site_name'), '', ['body' => 'db', 'css' => ['css/dashboard.css']]);
?>
<div class="auth">
  <div class="auth-art grain">
    <div class="orb orb-green"></div><div class="orb orb-gold"></div>
    <a class="logo logo-light" href="<?= e(base_url('index.php')) ?>"><?= site_logo(true) ?></a>
    <div>
      <h2>Content <em style="font-style:italic;color:var(--gold)">control,</em> beautifully simple.</h2>
      <p>Edit every section of the website, manage bookings, review submissions and verify professionals — from one secure dashboard.</p>
    </div>
    <small style="color:#ffffff73"><?= e(setting('powered_by')) ?></small>
  </div>
  <div class="auth-form">
    <form class="auth-box" method="post" novalidate>
      <span class="eyebrow">CMS Dashboard</span>
      <h1>Admin sign in</h1>
      <p>Authorised staff only.</p>
      <?php foreach ($flashes as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= icon('check-circle') ?><span><?= e($f['msg']) ?></span></div><?php endforeach; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= icon('x') ?><span><?= e($error) ?></span></div><?php endif; ?>
      <?= csrf_field() ?>
      <div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= e(post('email')) ?>" required autocomplete="username"></div>
      <div class="field"><label>Password</label><div class="pw-wrap"><input class="input" type="password" name="password" required autocomplete="current-password"><button type="button" class="pw-toggle" aria-label="Show password"><?= icon('eye') ?></button></div></div>
      <button class="btn btn-gold btn-block" type="submit"><span>Sign in to dashboard</span><?= icon('arrow') ?></button>
      <?php
      // Local development only, and only while the default password is still in use. Never shown when APP_DEBUG is false.
      $defAdmin = APP_DEBUG ? row('SELECT password FROM admins WHERE email = ?', ['admin@taskora.digital']) : null;
      if ($defAdmin && password_verify('Admin@123', $defAdmin['password'])): ?>
        <div class="demo-creds"><b>Default login</b> (local only: change it after first sign-in)<br><code>admin@taskora.digital</code> / <code>Admin@123</code></div>
      <?php endif; ?>
    </form>
  </div>
</div>
<script src="<?= asset('vendor/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= asset('js/dashboard.js') ?>"></script>
</body></html>
