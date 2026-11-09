<?php
require __DIR__ . '/_layout.php';
require_admin();

if (is_post()) {
    csrf_check();
    if (post('delete')) {
        q('DELETE FROM testimonials WHERE id = ?', [(int)post('delete')]);
        flash('success', 'Testimonial deleted.');
    } else {
        $id = (int)post('id');
        $stars = max(1, min(5, (int)post('stars')));
        if (post('text') === '' || post('author') === '') { flash('error', 'Text and author are required.'); redirect('admin/testimonials.php?edit=' . ($id ?: 'new')); }
        $f = [post('text'), post('author'), post('location'), $stars, (int)post('sort_order'), post('is_active') ? 1 : 0];
        if ($id) { $f[] = $id; q('UPDATE testimonials SET text=?, author=?, location=?, stars=?, sort_order=?, is_active=? WHERE id=?', $f); }
        else q('INSERT INTO testimonials (text, author, location, stars, sort_order, is_active) VALUES (?,?,?,?,?,?)', $f);
        flash('success', 'Testimonial saved.');
    }
    redirect('admin/testimonials.php');
}

$edit = get('edit');
if ($edit !== '') {
    $t = $edit === 'new' ? ['id' => 0, 'text' => '', 'author' => '', 'location' => '', 'stars' => 5, 'sort_order' => (int)val('SELECT MAX(sort_order) FROM testimonials') + 10, 'is_active' => 1]
                         : row('SELECT * FROM testimonials WHERE id = ?', [(int)$edit]);
    if (!$t) redirect('admin/testimonials.php');
    admin_start($t['id'] ? 'Edit testimonial' : 'New testimonial', 'Shown in the Ratings & Reviews slider', '<a class="b b-ghost" href="testimonials.php">' . icon('chev') . 'Back</a>');
    ?>
    <form method="post" class="card" style="max-width:800px"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
      <div class="card-body"><div class="form-grid">
        <div class="field full"><label>Review text</label><textarea class="input" name="text" required><?= e($t['text']) ?></textarea></div>
        <div class="field"><label>Author</label><input class="input" name="author" value="<?= e($t['author']) ?>" required></div>
        <div class="field"><label>Location</label><input class="input" name="location" value="<?= e($t['location']) ?>"></div>
        <div class="field"><label>Stars</label><select class="input" name="stars"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"<?= (int)$t['stars'] === $i ? ' selected' : '' ?>><?= str_repeat('★', $i) ?></option><?php endfor; ?></select></div>
        <div class="field"><label>Sort order</label><input class="input" type="number" name="sort_order" value="<?= (int)$t['sort_order'] ?>"></div>
        <div class="field"><label class="switch"><input type="checkbox" name="is_active" value="1"<?= $t['is_active'] ? ' checked' : '' ?>><i></i>Active</label></div>
      </div>
      <div class="form-actions"><a class="b b-ghost" href="testimonials.php">Cancel</a><button class="b b-gold"><?= icon('check') ?>Save</button></div></div>
    </form>
    <?php admin_end(); exit;
}

$list = rows('SELECT * FROM testimonials ORDER BY sort_order, id');
admin_start('Testimonials', count($list) . ' reviews', '<a class="b b-gold" href="?edit=new">' . icon('plus') . 'New testimonial</a>');
?>
<div class="card"><div class="table-wrap"><table class="tbl">
  <thead><tr><th>Review</th><th>Author</th><th>Stars</th><th>Status</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($list as $t): ?>
      <tr>
        <td style="max-width:460px"><?= e(mb_strimwidth($t['text'], 0, 120, '…')) ?></td>
        <td><b><?= e($t['author']) ?></b><br><span class="muted"><?= e($t['location']) ?></span></td>
        <td style="color:var(--gold)"><?= str_repeat('★', (int)$t['stars']) ?></td>
        <td><?= $t['is_active'] ? '<span class="pill pill-green">Active</span>' : '<span class="pill pill-neutral">Hidden</span>' ?></td>
        <td class="actions"><a class="b b-sm" href="?edit=<?= (int)$t['id'] ?>"><?= icon('edit') ?>Edit</a>
          <form method="post" style="display:inline"><?= csrf_field() ?><button class="b b-danger b-sm" name="delete" value="<?= (int)$t['id'] ?>" data-confirm-submit="Delete this testimonial?"><?= icon('trash') ?></button></form></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div>
<?php admin_end(); ?>
