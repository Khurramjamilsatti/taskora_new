<?php
require __DIR__ . '/_layout.php';
$me = require_admin();

if (is_post()) {
    csrf_check();
    $act = post('act');
    if ($act === 'password') {
        if (!password_verify(post('current'), $me['password'])) flash('error', 'Current password is incorrect.');
        elseif (strlen(post('new')) < 8) flash('error', 'New password must be at least 8 characters.');
        elseif (post('new') !== post('confirm')) flash('error', 'New passwords do not match.');
        else {
            q('UPDATE admins SET password = ? WHERE id = ?', [password_hash(post('new'), PASSWORD_DEFAULT), $me['id']]);
            session_regenerate_id(true);
            flash('success', 'Your password has been changed.');
        }
    } elseif ($act === 'profile') {
        $email = strtolower(post('email'));
        if (post('name') === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) flash('error', 'Enter a valid name and email.');
        elseif (val('SELECT id FROM admins WHERE email = ? AND id <> ?', [$email, $me['id']])) flash('error', 'That email is already used by another admin.');
        else { q('UPDATE admins SET name = ?, email = ? WHERE id = ?', [post('name'), $email, $me['id']]); flash('success', 'Profile updated.'); }
    } elseif ($act === 'add') {
        $email = strtolower(post('email'));
        if (post('name') === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen(post('password')) < 8) flash('error', 'Name, valid email and an 8+ character password are required.');
        elseif (val('SELECT id FROM admins WHERE email = ?', [$email])) flash('error', 'An admin with that email already exists.');
        else { q('INSERT INTO admins (name, email, password, role) VALUES (?,?,?,?)', [post('name'), $email, password_hash(post('password'), PASSWORD_DEFAULT), 'admin']); flash('success', 'Admin user added.'); }
    } elseif ($act === 'delete') {
        $id = (int)post('id');
        if ($id === (int)$me['id']) flash('error', 'You cannot delete your own account.');
        elseif ((int)val('SELECT COUNT(*) FROM admins') <= 1) flash('error', 'At least one admin is required.');
        else { q('DELETE FROM admins WHERE id = ?', [$id]); flash('success', 'Admin removed.'); }
    }
    redirect('admin/admins.php');
}

$list = rows('SELECT * FROM admins ORDER BY id');
$defaultPw = password_verify('Admin@123', $me['password']);
admin_start('Admin users', 'Who can access this dashboard');
?>
<?php if ($defaultPw): ?><div class="alert alert-error"><?= icon('lock') ?><span><b>You are still using the default password.</b> Change it below before going live.</span></div><?php endif; ?>
<div class="row-2e">
  <div>
    <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="act" value="profile">
      <div class="card-head"><div><h2>My profile</h2></div></div>
      <div class="card-body"><div class="form-grid">
        <div class="field"><label>Name</label><input class="input" name="name" value="<?= e($me['name']) ?>"></div>
        <div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= e($me['email']) ?>"></div>
      </div><div class="form-actions"><button class="b"><?= icon('check') ?>Save</button></div></div>
    </form>
    <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="act" value="password">
      <div class="card-head"><div><h2>Change my password</h2></div></div>
      <div class="card-body">
        <div class="field"><label>Current password</label><input class="input" type="password" name="current" required autocomplete="current-password"></div>
        <div class="form-grid">
          <div class="field"><label>New password</label><input class="input" type="password" name="new" minlength="8" required autocomplete="new-password"></div>
          <div class="field"><label>Confirm</label><input class="input" type="password" name="confirm" minlength="8" required autocomplete="new-password"></div>
        </div>
        <div class="form-actions"><button class="b b-gold"><?= icon('lock') ?>Update password</button></div>
      </div>
    </form>
  </div>
  <div>
    <div class="card"><div class="card-head"><div><h2>All admins</h2></div></div>
      <div class="table-wrap"><table class="tbl"><tbody>
        <?php foreach ($list as $a): ?>
          <tr><td><b><?= e($a['name']) ?></b><?= (int)$a['id'] === (int)$me['id'] ? ' <span class="pill pill-green">You</span>' : '' ?><br><span class="muted"><?= e($a['email']) ?></span></td>
            <td class="muted">Last login<br><?= $a['last_login'] ? e(time_ago($a['last_login'])) : 'Never' ?></td>
            <td class="actions"><?php if ((int)$a['id'] !== (int)$me['id']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><button class="b b-danger b-sm" data-confirm-submit="Remove <?= e($a['name']) ?>?"><?= icon('trash') ?></button></form><?php endif; ?></td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>
    <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="act" value="add">
      <div class="card-head"><div><h2>Add admin</h2></div></div>
      <div class="card-body">
        <div class="field"><label>Name</label><input class="input" name="name" required></div>
        <div class="form-grid">
          <div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
          <div class="field"><label>Password</label><input class="input" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
        </div>
        <div class="form-actions"><button class="b"><?= icon('plus') ?>Add admin</button></div>
      </div>
    </form>
  </div>
</div>
<?php admin_end(); ?>
