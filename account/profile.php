<?php
require __DIR__ . '/_layout.php';
$u = require_user();
$cats = array_column(rows('SELECT title FROM categories WHERE is_active = 1 ORDER BY sort_order'), 'title');

if (is_post()) {
    csrf_check();
    $name = post('name'); $phone = post('phone'); $city = post('city');
    $err = '';
    if (mb_strlen($name) < 3) $err = 'Please enter your full name.';
    elseif (!preg_match('/^[+\d][\d\s-]{6,19}$/', $phone)) $err = 'Enter a valid mobile number.';
    elseif (!in_array($city, cities(), true)) $err = 'Please choose a city.';
    if ($err) {
        flash('error', $err);
    } else {
        q('UPDATE users SET name = ?, phone = ?, city = ? WHERE id = ?', [$name, $phone, $city, $u['id']]);
        if ($u['role'] === 'provider') {
            $cat = in_array(post('category'), $cats, true) ? post('category') : $u['category'];
            q('UPDATE users SET category = ?, subcategory = ?, experience = ?, service_areas = ?, qualifications = ? WHERE id = ?',
              [$cat, post('subcategory'), (int)post('experience'), post('service_areas'), post('qualifications'), $u['id']]);
        }
        flash('success', 'Profile updated — your account details have been saved.');
    }
    redirect('account/profile.php');
}

portal_start('Profile settings', 'Update your account details');
?>
<form class="card" method="post" style="max-width:860px">
  <?= csrf_field() ?>
  <div class="card-head"><div><h2>Account</h2><p>Signed in as <?= e($u['email']) ?> · <?= e(ucfirst($u['role'])) ?></p></div><?= $u['role'] === 'provider' ? '<span class="pill pill-' . ($u['status'] === 'active' ? 'green' : 'gold') . '">' . e(ucfirst($u['status'])) . '</span>' : '' ?></div>
  <div class="card-body">
    <div class="form-grid">
      <div class="field"><label>Full name</label><input class="input" name="name" value="<?= e($u['name']) ?>" required></div>
      <div class="field"><label>Email</label><input class="input" value="<?= e($u['email']) ?>" disabled></div>
      <div class="field"><label>Mobile</label><input class="input" name="phone" value="<?= e($u['phone']) ?>" required></div>
      <div class="field"><label>City</label><select class="input" name="city"><?php foreach (cities() as $c): ?><option<?= $u['city'] === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
    </div>
    <?php if ($u['role'] === 'provider'): ?>
      <h3 style="font-family:var(--f-display);font-weight:400;margin:20px 0 14px">Professional profile</h3>
      <div class="form-grid">
        <div class="field"><label>Main category</label><select class="input" name="category"><?php foreach ($cats as $c): ?><option<?= $u['category'] === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Specialisation</label><input class="input" name="subcategory" value="<?= e($u['subcategory']) ?>"></div>
        <div class="field"><label>Experience (years)</label><input class="input" type="number" min="0" name="experience" value="<?= e($u['experience']) ?>"></div>
        <div class="field"><label>Service areas</label><input class="input" name="service_areas" value="<?= e($u['service_areas']) ?>"></div>
        <div class="field full"><label>Qualifications</label><textarea class="input" name="qualifications"><?= e($u['qualifications']) ?></textarea></div>
      </div>
    <?php endif; ?>
    <div class="form-actions"><button class="b b-gold" type="submit"><?= icon('check') ?>Save profile</button></div>
  </div>
</form>
<?php portal_end(); ?>
