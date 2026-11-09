<?php
require __DIR__ . '/_layout.php';
require_admin();

if (is_post()) {
    csrf_check();
    if (post('toggle')) {
        q('UPDATE sections SET is_active = 1 - is_active WHERE id = ?', [(int)post('toggle')]);
        flash('success', 'Section visibility updated.');
    }
    redirect('admin/sections.php');
}

$pages = ['home' => 'Home page', 'catalogue' => 'Catalogue page'];
$all = rows('SELECT id, skey, page, label, tag, title, is_active, updated_at FROM sections ORDER BY page, sort_order, id');

admin_start('Page sections', 'Edit every heading, paragraph, list and number shown on the website');
foreach ($pages as $pk => $pl): ?>
<div class="card">
  <div class="card-head"><div><h2><?= e($pl) ?></h2><p>Sections are shown in this order. Toggle to hide a section without deleting it.</p></div>
    <a class="b b-ghost b-sm" href="<?= e(base_url($pk === 'home' ? 'index.php' : 'catalogue.php')) ?>" target="_blank"><?= icon('eye') ?>Preview</a></div>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th style="width:40px">#</th><th>Section</th><th>Heading</th><th>Updated</th><th>Visible</th><th></th></tr></thead>
    <tbody>
      <?php $n = 1; foreach ($all as $s): if ($s['page'] !== $pk) continue; ?>
        <tr>
          <td class="muted mono"><?= str_pad($n++, 2, '0', STR_PAD_LEFT) ?></td>
          <td><b><?= e($s['label']) ?></b><br><span class="muted mono" style="font-size:.72rem"><?= e($s['skey']) ?></span></td>
          <td style="max-width:360px"><?= e(mb_strimwidth(str_replace("\n", ' ', $s['title'] ?: $s['tag']), 0, 80, '…')) ?></td>
          <td class="muted"><?= e(time_ago($s['updated_at'])) ?></td>
          <td>
            <form method="post" style="margin:0"><?= csrf_field() ?><input type="hidden" name="toggle" value="<?= (int)$s['id'] ?>">
              <label class="switch"><input type="checkbox" onchange="this.form.submit()"<?= $s['is_active'] ? ' checked' : '' ?>><i></i></label>
            </form>
          </td>
          <td class="actions"><a class="b b-sm" href="section-edit.php?id=<?= (int)$s['id'] ?>"><?= icon('edit') ?>Edit</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php endforeach;
admin_end();
