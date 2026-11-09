<?php
require __DIR__ . '/_layout.php';
require_admin();

if (is_post()) {
    csrf_check();
    q('DELETE FROM subscribers WHERE id = ?', [(int)post('delete')]);
    flash('success', 'Subscriber removed.');
    redirect('admin/subscribers.php');
}
if (get('export') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="subscribers-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Email', 'Subscribed']);
    foreach (rows('SELECT email, created_at FROM subscribers ORDER BY id DESC') as $r) fputcsv($out, array_values($r));
    exit;
}
$list = rows('SELECT * FROM subscribers ORDER BY id DESC');
admin_start('Newsletter subscribers', count($list) . ' subscriber(s)', '<a class="b b-ghost" href="?export=csv">' . icon('upload') . 'Export CSV</a>');
?>
<div class="card">
  <?php if (!$list): ?><div class="empty"><?= icon('mail') ?>No subscribers yet. The footer newsletter form feeds this list.</div><?php else: ?>
  <div class="table-wrap"><table class="tbl"><thead><tr><th>Email</th><th>Subscribed</th><th></th></tr></thead><tbody>
    <?php foreach ($list as $s): ?><tr><td><?= e($s['email']) ?></td><td class="muted"><?= e(date('d M Y, g:i A', strtotime($s['created_at']))) ?></td>
      <td class="actions"><form method="post"><?= csrf_field() ?><button class="b b-danger b-sm" name="delete" value="<?= (int)$s['id'] ?>" data-confirm-submit="Remove this subscriber?"><?= icon('trash') ?></button></form></td></tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
</div>
<?php admin_end(); ?>
