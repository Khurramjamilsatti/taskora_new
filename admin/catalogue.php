<?php
require __DIR__ . '/_layout.php';
require_admin();
$tones = ['green' => 'Emerald', 'green-dark' => 'Forest', 'black' => 'Black', 'gold' => 'Gold'];

if (is_post()) {
    csrf_check();
    if (post('delete')) {
        q('DELETE FROM catalogue_groups WHERE id = ?', [(int)post('delete')]);
        flash('success', 'Catalogue group deleted.');
        redirect('admin/catalogue.php');
    }
    $id = (int)post('id');
    if (post('name') === '') { flash('error', 'Name is required.'); redirect('admin/catalogue.php?edit=' . ($id ?: 'new')); }
    $f = [post('name'), post('count_label'), isset($tones[post('tone')]) ? post('tone') : 'green', post('note'), implode("\n", lines(post('body'))), (int)post('sort_order'), post('is_active') ? 1 : 0];
    if ($id) { $f[] = $id; q('UPDATE catalogue_groups SET name=?, count_label=?, tone=?, note=?, body=?, sort_order=?, is_active=? WHERE id=?', $f); }
    else q('INSERT INTO catalogue_groups (name, count_label, tone, note, body, sort_order, is_active) VALUES (?,?,?,?,?,?,?)', $f);
    flash('success', 'Catalogue group saved.');
    redirect('admin/catalogue.php');
}

$edit = get('edit');
if ($edit !== '') {
    $c = $edit === 'new' ? ['id' => 0, 'name' => '', 'count_label' => '', 'tone' => 'green', 'note' => '', 'body' => '', 'sort_order' => (int)val('SELECT MAX(sort_order) FROM catalogue_groups') + 10, 'is_active' => 1]
                         : row('SELECT * FROM catalogue_groups WHERE id = ?', [(int)$edit]);
    if (!$c) redirect('admin/catalogue.php');
    admin_start($c['id'] ? 'Edit catalogue group' : 'New catalogue group', 'Shown as an expandable panel on the Catalogue page', '<a class="b b-ghost" href="catalogue.php">' . icon('chev') . 'Back</a>');
    ?>
    <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
      <div class="card-body">
        <div class="form-grid">
          <div class="field"><label>Group name</label><input class="input" name="name" value="<?= e($c['name']) ?>" required></div>
          <div class="field"><label>Count label</label><input class="input" name="count_label" value="<?= e($c['count_label']) ?>" placeholder="e.g. 20 specialisations"></div>
          <div class="field"><label>Accent colour</label><select class="input" name="tone"><?php foreach ($tones as $k => $l): ?><option value="<?= $k ?>"<?= $c['tone'] === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
          <div class="field"><label>Sort order</label><input class="input" type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>"></div>
          <div class="field full"><label>Specialisations</label><textarea class="input" name="body" rows="10"><?= e($c['body']) ?></textarea>
            <div class="hint">Comma-separated chips. For sub-groups use one line each in the format <code>Sub-group title: chip, chip, chip</code></div></div>
          <div class="field full"><label>Note (optional)</label><textarea class="input" name="note"><?= e($c['note']) ?></textarea></div>
          <div class="field"><label class="switch"><input type="checkbox" name="is_active" value="1"<?= $c['is_active'] ? ' checked' : '' ?>><i></i>Active</label></div>
        </div>
        <div class="form-actions"><a class="b b-ghost" href="catalogue.php">Cancel</a><button class="b b-gold"><?= icon('check') ?>Save group</button></div>
      </div>
    </form>
    <?php admin_end(); exit;
}

$list = rows('SELECT * FROM catalogue_groups ORDER BY sort_order, id');
admin_start('Catalogue groups', 'Master professional catalogue', '<a class="b b-gold" href="?edit=new">' . icon('plus') . 'New group</a>');
?>
<div class="card"><div class="table-wrap"><table class="tbl">
  <thead><tr><th>Group</th><th>Label</th><th>Chips</th><th>Status</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($list as $c): $chips = 0; foreach (lines($c['body']) as $l) { $p = strpos($l, ':') !== false ? substr($l, strpos($l, ':') + 1) : $l; $chips += count(array_filter(array_map('trim', explode(',', $p)))); } ?>
      <tr>
        <td><span class="acc-tone tone-<?= e($c['tone']) ?>" style="display:inline-block;height:22px;width:8px;vertical-align:middle;margin-right:10px;border-radius:4px"></span><b><?= e($c['name']) ?></b></td>
        <td class="muted"><?= e($c['count_label']) ?></td><td><?= $chips ?></td>
        <td><?= $c['is_active'] ? '<span class="pill pill-green">Active</span>' : '<span class="pill pill-neutral">Hidden</span>' ?></td>
        <td class="actions"><a class="b b-sm" href="?edit=<?= (int)$c['id'] ?>"><?= icon('edit') ?>Edit</a>
          <form method="post" style="display:inline"><?= csrf_field() ?><button class="b b-danger b-sm" name="delete" value="<?= (int)$c['id'] ?>" data-confirm-submit="Delete this group?"><?= icon('trash') ?></button></form></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div>
<?php admin_end(); ?>
