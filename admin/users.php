<?php
require __DIR__ . '/_layout.php';
require_admin();

if (is_post()) {
    csrf_check();
    $id = (int)post('id');
    $u = row('SELECT * FROM users WHERE id = ?', [$id]);
    if ($u) {
        if (post('delete')) {
            if ((int)val('SELECT COUNT(*) FROM bookings WHERE customer_id = ? OR provider_id = ?', [$id, $id])) {
                flash('error', 'This user has bookings. Suspend the account instead of deleting it.');
            } else {
                q('DELETE FROM notifications WHERE user_id = ?', [$id]);
                q('DELETE FROM users WHERE id = ?', [$id]);
                flash('success', 'User deleted.');
            }
        } elseif (in_array(post('status'), ['pending', 'active', 'suspended'], true)) {
            q('UPDATE users SET status = ? WHERE id = ?', [post('status'), $id]);
            if (post('status') === 'active' && $u['status'] !== 'active' && $u['role'] === 'provider') {
                notify($id, 'Your profile is verified 🎉', 'You can now accept booking requests on Taskora.', 'account/requests.php');
            }
            flash('success', $u['name'] . ' is now ' . post('status') . '.');
        }
    }
    redirect('admin/users.php?' . http_build_query(['role' => get('role'), 'status' => get('status')]));
}

$role = get('role') === 'provider' ? 'provider' : (get('role') === 'customer' ? 'customer' : '');
$st = get('status');
$where = ['1=1']; $args = [];
if ($role) { $where[] = 'u.role = ?'; $args[] = $role; }
if (in_array($st, ['pending', 'active', 'suspended'], true)) { $where[] = 'u.status = ?'; $args[] = $st; }
ensure_pro_tables();
$list = rows('SELECT u.*,
    (SELECT a.id FROM provider_applications a WHERE a.user_id = u.id ORDER BY a.id DESC LIMIT 1) AS app_id,
    (SELECT COUNT(*) FROM bookings b WHERE b.customer_id = u.id OR b.provider_id = u.id) AS jobs,
    (SELECT ROUND(AVG(f.rating),1) FROM booking_feedback f JOIN bookings b ON b.id = f.booking_id WHERE b.provider_id = u.id) AS rating
    FROM users u WHERE ' . implode(' AND ', $where) . ' ORDER BY u.id DESC LIMIT 500', $args);

admin_start('Customers & providers', count($list) . ' account(s)');
?>
<div class="card">
  <div class="card-head">
    <div class="tabs">
      <a href="users.php" class="<?= !$role && !$st ? 'active' : '' ?>">Everyone</a>
      <a href="?role=customer" class="<?= $role === 'customer' ? 'active' : '' ?>">Customers</a>
      <a href="?role=provider" class="<?= $role === 'provider' && $st !== 'pending' ? 'active' : '' ?>">Providers</a>
      <a href="?role=provider&status=pending" class="<?= $st === 'pending' ? 'active' : '' ?>">Awaiting verification</a>
    </div>
    <div class="filters"><input class="input search" placeholder="Search name, email, phone…" data-table-search="#userTable"></div>
  </div>
  <?php if (!$list): ?><div class="empty"><?= icon('users') ?>No accounts found.</div><?php else: ?>
  <div class="table-wrap"><table class="tbl" id="userTable">
    <thead><tr><th>Name</th><th>Role</th><th>Contact</th><th>City</th><th>Professional</th><th>Jobs</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($list as $u): ?>
        <tr>
          <td><b><?= e($u['name']) ?></b><br><span class="muted">Joined <?= e(date('d M Y', strtotime($u['created_at']))) ?></span></td>
          <td><span class="pill pill-<?= $u['role'] === 'provider' ? 'gold' : 'neutral' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
          <td><?= e($u['email']) ?><br><span class="muted"><?= e($u['phone']) ?></span></td>
          <td><?= e($u['city']) ?></td>
          <td><?php if ($u['role'] === 'provider'): ?><?= e($u['category']) ?><br><span class="muted"><?= e($u['subcategory']) ?> · <?= (int)$u['experience'] ?> yrs<?= $u['cnic'] ? ' · CNIC ' . e($u['cnic']) : '' ?><?= $u['rating'] ? ' · ' . e($u['rating']) . '★' : '' ?></span><?php else: ?><span class="muted">—</span><?php endif; ?></td>
          <td><?= (int)$u['jobs'] ?></td>
          <td><span class="pill pill-<?= $u['status'] === 'active' ? 'green' : ($u['status'] === 'pending' ? 'gold' : 'red') ?>"><?= e(ucfirst($u['status'])) ?></span></td>
          <td class="actions">
            <?php if ($u['app_id']): ?><a class="b b-ghost b-sm" href="applications.php?id=<?= (int)$u['app_id'] ?>"><?= icon('doc') ?>Application</a><?php endif; ?>
            <form method="post" style="display:inline-flex;gap:6px"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <?php if ($u['status'] !== 'active'): ?><button class="b b-sm" name="status" value="active"><?= icon('check') ?><?= $u['role'] === 'provider' && $u['status'] === 'pending' ? 'Verify' : 'Activate' ?></button><?php endif; ?>
              <?php if ($u['status'] !== 'suspended'): ?><button class="b b-ghost b-sm" name="status" value="suspended" data-confirm-submit="Suspend <?= e($u['name']) ?>?">Suspend</button><?php endif; ?>
              <button class="b b-danger b-sm" name="delete" value="1" data-confirm-submit="Delete <?= e($u['name']) ?> permanently?"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div><?php endif; ?>
</div>
<?php admin_end(); ?>
