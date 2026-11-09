<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if (current_user()) redirect('account/index.php');

$error = '';
$role = post('role', get('role', 'customer')) === 'provider' ? 'provider' : 'customer';
if (is_post()) {
    csrf_check();
    if (!throttle('login', 8, 300)) {
        $error = 'Too many attempts. Please wait a few minutes and try again.';
    } else {
        $u = row('SELECT * FROM users WHERE email = ? AND role = ?', [strtolower(post('email')), $role]);
        if (!$u || !password_verify(post('password'), $u['password'])) {
            $error = 'Incorrect email or password for a ' . $role . ' account.';
        } elseif ($u['status'] === 'suspended') {
            $error = 'This account is suspended. Please contact support.';
        } else {
            session_regenerate_id(true);
            $_SESSION['uid'] = $u['id'];
            flash('success', 'Welcome back, ' . $u['name'] . '.');
            redirect('account/index.php');
        }
    }
}
$flashes = flash();

render_head('Sign in | ' . setting('site_name'), '', ['body' => 'db', 'css' => ['css/dashboard.css']]);
?>
<div class="auth">
  <div class="auth-art grain">
    <div class="orb orb-green"></div><div class="orb orb-gold"></div>
    <a class="logo logo-light" href="<?= e(base_url('index.php')) ?>"><?= site_logo(true) ?></a>
    <div>
      <h2>Welcome <em style="font-style:italic;color:var(--gold)">back.</em></h2>
      <p>Manage bookings, chat with your professional and track every job from one calm dashboard.</p>
      <ul class="auth-points">
        <li><?= icon('check-circle') ?>Live booking status &amp; quotations</li>
        <li><?= icon('check-circle') ?>Secure chat once your deal is locked</li>
        <li><?= icon('check-circle') ?>Ratings that keep quality high</li>
      </ul>
    </div>
    <small style="color:#ffffff73"><?= e(setting('powered_by')) ?></small>
  </div>
  <div class="auth-form">
    <form class="auth-box" method="post" novalidate>
      <h1>Sign in</h1>
      <p>Access your customer or provider dashboard.</p>
      <?php foreach ($flashes as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= icon('check-circle') ?><span><?= e($f['msg']) ?></span></div><?php endforeach; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= icon('x') ?><span><?= e($error) ?></span></div><?php endif; ?>
      <?= csrf_field() ?>
      <span class="label" style="display:block;font-size:.72rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:10px">I am signing in as</span>
      <div class="role-switch">
        <label><input type="radio" name="role" value="customer"<?= $role === 'customer' ? ' checked' : '' ?>><span><?= icon('user') ?>Customer</span></label>
        <label><input type="radio" name="role" value="provider"<?= $role === 'provider' ? ' checked' : '' ?>><span><?= icon('briefcase') ?>Professional</span></label>
      </div>
      <div class="field"><label for="email">Email</label><input class="input" type="email" id="email" name="email" value="<?= e(post('email')) ?>" required autocomplete="email"></div>
      <div class="field"><label for="password">Password</label><div class="pw-wrap"><input class="input" type="password" id="password" name="password" required autocomplete="current-password"><button type="button" class="pw-toggle" aria-label="Show password"><?= icon('eye') ?></button></div></div>
      <button class="btn btn-gold btn-block" type="submit"><span>Sign in</span><?= icon('arrow') ?></button>
      <div class="auth-alt">New to Taskora? <a href="register.php">Create customer account</a> · <a href="register.php?role=provider">Join as a professional</a></div>
    </form>
  </div>
</div>
<div class="toast-stack" id="toasts"></div>
<script src="<?= asset('vendor/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= asset('js/dashboard.js') ?>"></script>
</body></html>
