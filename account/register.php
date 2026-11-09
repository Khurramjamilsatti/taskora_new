<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if (current_user()) redirect('account/index.php');

$role = (post('role', get('role')) === 'provider') ? 'provider' : 'customer';
if ($role === 'provider') { require __DIR__ . '/_register_pro.php'; exit; }
$cats = rows('SELECT title, items FROM categories WHERE is_active = 1 ORDER BY sort_order');
$errors = [];

if (is_post()) {
    csrf_check();
    $d = [
        'name' => post('name'), 'email' => strtolower(post('email')), 'phone' => post('phone'), 'city' => post('city'),
        'password' => post('password'), 'password2' => post('password2'),
        'cnic' => post('cnic'), 'category' => post('category'), 'subcategory' => post('subcategory'),
        'experience' => post('experience'), 'service_areas' => post('service_areas'), 'qualifications' => post('qualifications'),
    ];
    if (mb_strlen($d['name']) < 3) $errors['name'] = 'Please enter your full name.';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    elseif (val('SELECT id FROM users WHERE email = ?', [$d['email']])) $errors['email'] = 'An account with this email already exists.';
    if (!preg_match('/^[+\d][\d\s-]{6,19}$/', $d['phone'])) $errors['phone'] = 'Enter a valid mobile number.';
    if (!in_array($d['city'], cities(), true)) $errors['city'] = 'Please choose your city.';
    if (strlen($d['password']) < 8) $errors['password'] = 'Use at least 8 characters.';
    elseif ($d['password'] !== $d['password2']) $errors['password2'] = 'Passwords do not match.';
    if ($role === 'provider') {
        if (!preg_match('/^\d{5}-?\d{7}-?\d$/', $d['cnic'])) $errors['cnic'] = 'Use the format XXXXX-XXXXXXX-X.';
        if (!in_array($d['category'], array_column($cats, 'title'), true)) $errors['category'] = 'Choose your main category.';
        if ($d['subcategory'] === '') $errors['subcategory'] = 'Enter your specialisation.';
        if ($d['experience'] === '' || !ctype_digit($d['experience'])) $errors['experience'] = 'Enter years of experience.';
        if ($d['service_areas'] === '') $errors['service_areas'] = 'List the areas you cover.';
    }
    if (!$errors && !throttle('register', 5, 600)) $errors['email'] = 'Too many sign-ups from this session. Try again later.';

    if (!$errors) {
        q('INSERT INTO users (role, name, email, phone, cnic, city, password, category, subcategory, experience, service_areas, qualifications, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $role, $d['name'], $d['email'], $d['phone'], $d['cnic'] ?: null, $d['city'], password_hash($d['password'], PASSWORD_DEFAULT),
            $role === 'provider' ? $d['category'] : null, $role === 'provider' ? $d['subcategory'] : null,
            $role === 'provider' ? (int)$d['experience'] : null, $role === 'provider' ? $d['service_areas'] : null,
            $role === 'provider' ? $d['qualifications'] : null, $role === 'provider' ? 'pending' : 'active',
        ]);
        $id = db()->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['uid'] = $id;
        notify($id, $role === 'provider' ? 'Application received' : 'Welcome to Taskora',
            $role === 'provider' ? 'Your profile is under verification. You can explore the workspace meanwhile.' : 'Your customer account is ready — book your first service any time.', 'account/index.php');
        flash('success', $role === 'provider' ? 'Application submitted — our team will verify your profile shortly.' : 'Welcome to Taskora! Your account is ready.');
        redirect('account/index.php');
    }
}
function err($errors, $k) { return isset($errors[$k]) ? '<div class="field-error" style="display:block">' . e($errors[$k]) . '</div>' : ''; }
function v($k) { return e(post($k)); }

render_head(($role === 'provider' ? 'Join as a professional' : 'Create account') . ' | ' . setting('site_name'), '', ['body' => 'db', 'css' => ['css/dashboard.css']]);
?>
<div class="auth">
  <div class="auth-art grain">
    <div class="orb orb-green"></div><div class="orb orb-gold"></div>
    <a class="logo logo-light" href="<?= e(base_url('index.php')) ?>"><?= site_logo(true) ?></a>
    <div>
      <?php if ($role === 'provider'): ?>
        <h2>Grow with <em style="font-style:italic;color:var(--gold)">Taskora.</em></h2>
        <p>Registration → Verification → Training → Activation. Then a steady stream of nearby jobs and weekly payouts.</p>
        <ul class="auth-points">
          <li><?= icon('check-circle') ?>More customers, less chasing</li>
          <li><?= icon('check-circle') ?>Weekly payouts &amp; performance rewards</li>
          <li><?= icon('check-circle') ?>A verified digital reputation</li>
        </ul>
      <?php else: ?>
        <h2>Your trusted <em style="font-style:italic;color:var(--gold)">pros,</em> one tap away.</h2>
        <p>Book services, follow every job live and keep your favourite professionals close.</p>
        <ul class="auth-points">
          <li><?= icon('check-circle') ?>Verified professionals only</li>
          <li><?= icon('check-circle') ?>Upfront quotations, no surprises</li>
          <li><?= icon('check-circle') ?>24/7 support and complaint resolution</li>
        </ul>
      <?php endif; ?>
    </div>
    <small style="color:#ffffff73"><?= e(setting('powered_by')) ?></small>
  </div>
  <div class="auth-form">
    <form class="auth-box" method="post" novalidate style="max-width:<?= $role === 'provider' ? '620px' : '460px' ?>">
      <h1><?= $role === 'provider' ? 'Join as a professional' : 'Create account' ?></h1>
      <p><?= $role === 'provider' ? 'Tell us about your skills — we verify every profile before activation.' : 'Book services, track jobs and manage your Taskora dashboard.' ?></p>
      <?= csrf_field() ?>
      <div class="role-switch">
        <a href="register.php" style="display:block"><span style="<?= $role === 'customer' ? 'background:#fff;color:var(--green-deep);box-shadow:var(--shadow-sm)' : '' ?>"><?= icon('user') ?>Customer</span></a>
        <a href="register.php?role=provider" style="display:block"><span style="<?= $role === 'provider' ? 'background:#fff;color:var(--green-deep);box-shadow:var(--shadow-sm)' : '' ?>"><?= icon('briefcase') ?>Professional</span></a>
      </div>
      <input type="hidden" name="role" value="<?= e($role) ?>">
      <div class="form-grid">
        <div class="field full"><label>Full name</label><input class="input" name="name" value="<?= v('name') ?>" required><?= err($errors, 'name') ?></div>
        <div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= v('email') ?>" required><?= err($errors, 'email') ?></div>
        <div class="field"><label>Mobile</label><input class="input" type="tel" name="phone" value="<?= v('phone') ?>" placeholder="03XX XXXXXXX" required><?= err($errors, 'phone') ?></div>
        <div class="field<?= $role === 'customer' ? ' full' : '' ?>"><label>City</label><select class="input" name="city"><?php foreach (cities() as $c): ?><option<?= post('city') === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select><?= err($errors, 'city') ?></div>
        <?php if ($role === 'provider'): ?>
          <div class="field"><label>CNIC</label><input class="input" name="cnic" value="<?= v('cnic') ?>" placeholder="XXXXX-XXXXXXX-X"><?= err($errors, 'cnic') ?></div>
          <div class="field"><label>Main category</label><select class="input" name="category" id="regCat"><option value="">Select…</option><?php foreach ($cats as $c): ?><option data-items="<?= e(implode('|', lines($c['items']))) ?>"<?= post('category') === $c['title'] ? ' selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?></select><?= err($errors, 'category') ?></div>
          <div class="field"><label>Specialisation</label><input class="input" name="subcategory" id="regSub" list="subList" value="<?= v('subcategory') ?>"><datalist id="subList"></datalist><?= err($errors, 'subcategory') ?></div>
          <div class="field"><label>Years of experience</label><input class="input" type="number" min="0" name="experience" value="<?= v('experience') ?>"><?= err($errors, 'experience') ?></div>
          <div class="field"><label>Service areas</label><input class="input" name="service_areas" value="<?= v('service_areas') ?>" placeholder="Cities / zones covered"><?= err($errors, 'service_areas') ?></div>
          <div class="field full"><label>Qualifications &amp; certifications</label><textarea class="input" name="qualifications" style="min-height:80px"><?= v('qualifications') ?></textarea></div>
        <?php endif; ?>
        <div class="field"><label>Password</label><div class="pw-wrap"><input class="input" type="password" name="password" required autocomplete="new-password"><button type="button" class="pw-toggle" aria-label="Show password"><?= icon('eye') ?></button></div><?= err($errors, 'password') ?></div>
        <div class="field"><label>Confirm password</label><input class="input" type="password" name="password2" required autocomplete="new-password"><?= err($errors, 'password2') ?></div>
      </div>
      <button class="btn btn-gold btn-block" type="submit"><span><?= $role === 'provider' ? 'Submit application' : 'Create account' ?></span><?= icon('arrow') ?></button>
      <div class="auth-alt">Already registered? <a href="login.php<?= $role === 'provider' ? '?role=provider' : '' ?>">Sign in</a></div>
    </form>
  </div>
</div>
<div class="toast-stack" id="toasts"></div>
<script src="<?= asset('vendor/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= asset('js/dashboard.js') ?>"></script>
<script>
$('#regCat').on('change', function () {
  var items = ($(this).find(':selected').data('items') || '').split('|');
  $('#subList').html(items.map(function (i) { return '<option value="' + $('<i>').text(i).html() + '">'; }).join(''));
}).trigger('change');
</script>
</body></html>
