<?php
require __DIR__ . '/_layout.php';
$u = require_user();

if (is_post()) {
    csrf_check();
    if (!password_verify(post('current'), $u['password'])) flash('error', 'Your current password is incorrect.');
    elseif (strlen(post('new')) < 8) flash('error', 'Use a strong password of at least 8 characters.');
    elseif (post('new') !== post('confirm')) flash('error', 'The new passwords do not match.');
    else {
        q('UPDATE users SET password = ? WHERE id = ?', [password_hash(post('new'), PASSWORD_DEFAULT), $u['id']]);
        session_regenerate_id(true);
        flash('success', 'Password updated — use your new password next time you sign in.');
    }
    redirect('account/password.php');
}

portal_start('Change password', 'Keep your account secure');
?>
<form class="card" method="post" style="max-width:560px">
  <?= csrf_field() ?>
  <div class="card-head"><div><h2>Update password</h2><p>Use at least 8 characters</p></div></div>
  <div class="card-body">
    <div class="field"><label>Current password</label><div class="pw-wrap"><input class="input" type="password" name="current" required autocomplete="current-password"><button type="button" class="pw-toggle"><?= icon('eye') ?></button></div></div>
    <div class="field"><label>New password</label><div class="pw-wrap"><input class="input" type="password" name="new" minlength="8" required autocomplete="new-password"><button type="button" class="pw-toggle"><?= icon('eye') ?></button></div></div>
    <div class="field"><label>Confirm new password</label><input class="input" type="password" name="confirm" minlength="8" required autocomplete="new-password"></div>
    <div class="form-actions"><button class="b b-gold" type="submit"><?= icon('lock') ?>Update password</button></div>
  </div>
</form>
<?php portal_end(); ?>
