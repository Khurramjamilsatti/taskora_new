<?php
require __DIR__ . '/_layout.php';
require_admin();

$icons = ['home', 'build', 'compass', 'scale', 'calc', 'code', 'book', 'briefcase', 'building', 'users', 'megaphone', 'heart', 'calendar', 'truck', 'shield', 'coins', 'bolt', 'sun', 'layers', 'globe', 'spark'];

if (is_post()) {
    csrf_check();
    if (post('delete')) {
        q('DELETE FROM categories WHERE id = ?', [(int)post('delete')]);
        flash('success', 'Category deleted.');
        redirect('admin/categories.php');
    }
    $id = (int)post('id');
    $title = post('title');
    $slug = slugify(post('slug') ?: $title);
    if ($title === '') { flash('error', 'Title is required.'); redirect('admin/categories.php?edit=' . ($id ?: 'new')); }
    if (val('SELECT id FROM categories WHERE slug = ? AND id <> ?', [$slug, $id])) $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 3);
    $f = [post('code'), $title, $slug, in_array(post('icon'), $icons, true) ? post('icon') : 'spark', post('short_desc'), post('description'),
          implode("\n", lines(post('items'))), post('meta_title'), post('meta_description'), (int)post('sort_order'), post('is_active') ? 1 : 0];
    if ($id) {
        $f[] = $id;
        q('UPDATE categories SET code=?, title=?, slug=?, icon=?, short_desc=?, description=?, items=?, meta_title=?, meta_description=?, sort_order=?, is_active=? WHERE id=?', $f);
    } else {
        q('INSERT INTO categories (code, title, slug, icon, short_desc, description, items, meta_title, meta_description, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?)', $f);
    }
    flash('success', 'Category “' . $title . '” saved.');
    redirect('admin/categories.php');
}

$edit = get('edit');
if ($edit !== '') {
    $c = $edit === 'new'
        ? ['id' => 0, 'code' => 'T' . str_pad((int)val('SELECT COUNT(*) FROM categories') + 1, 2, '0', STR_PAD_LEFT), 'title' => '', 'slug' => '', 'icon' => 'spark', 'short_desc' => '', 'description' => '', 'items' => '', 'meta_title' => '', 'meta_description' => '', 'sort_order' => ((int)val('SELECT MAX(sort_order) FROM categories') + 10), 'is_active' => 1]
        : row('SELECT * FROM categories WHERE id = ?', [(int)$edit]);
    if (!$c) redirect('admin/categories.php');
    admin_start($c['id'] ? 'Edit category' : 'New category', 'Shown in the services directory, footer and its own SEO page',
        '<a class="b b-ghost" href="categories.php">' . icon('chev') . 'Back</a>');
    ?>
    <form method="post" class="card">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
      <div class="card-body">
        <div class="form-grid">
          <div class="field"><label>Title</label><input class="input" name="title" value="<?= e($c['title']) ?>" data-slug-source="#slug" required></div>
          <div class="field"><label>Slug (URL)</label><input class="input slug-input" id="slug" name="slug" value="<?= e($c['slug']) ?>"><div class="hint">service.php?slug=<b><?= e($c['slug'] ?: '…') ?></b></div></div>
          <div class="field"><label>Code</label><input class="input" name="code" value="<?= e($c['code']) ?>"></div>
          <div class="field"><label>Icon</label>
            <div style="display:flex;flex-wrap:wrap;gap:6px">
              <?php foreach ($icons as $ic): ?>
                <label style="margin:0;cursor:pointer"><input type="radio" name="icon" value="<?= $ic ?>"<?= $c['icon'] === $ic ? ' checked' : '' ?> style="display:none" onchange="$(this).closest('div').find('span').css({background:'',color:''});$(this).next().css({background:'var(--green)',color:'#fff'})"><span class="db-icon-btn" title="<?= $ic ?>" style="<?= $c['icon'] === $ic ? 'background:var(--green);color:#fff' : '' ?>"><?= icon($ic) ?></span></label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="field full"><label>Short description</label><input class="input" name="short_desc" value="<?= e($c['short_desc']) ?>" maxlength="255"></div>
          <div class="field full"><label>Services in this category — one per line</label><textarea class="input" name="items" rows="6"><?= e($c['items']) ?></textarea></div>
          <div class="field full"><label>Detail page content (HTML allowed: &lt;p&gt; &lt;h2&gt; &lt;ul&gt; &lt;li&gt; &lt;strong&gt; &lt;a&gt;)</label><textarea class="input code" name="description" style="min-height:180px"><?= e($c['description']) ?></textarea></div>
          <div class="field"><label>SEO title</label><input class="input" name="meta_title" value="<?= e($c['meta_title']) ?>"></div>
          <div class="field"><label>SEO description</label><input class="input" name="meta_description" value="<?= e($c['meta_description']) ?>" maxlength="300"></div>
          <div class="field"><label>Sort order</label><input class="input" type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>"></div>
          <div class="field"><label>&nbsp;</label><label class="switch"><input type="checkbox" name="is_active" value="1"<?= $c['is_active'] ? ' checked' : '' ?>><i></i>Active</label></div>
        </div>
        <div class="form-actions"><a class="b b-ghost" href="categories.php">Cancel</a><button class="b b-gold"><?= icon('check') ?>Save category</button></div>
      </div>
    </form>
    <?php
    admin_end();
    exit;
}

$list = rows('SELECT * FROM categories ORDER BY sort_order, id');
admin_start('Service categories', count($list) . ' categories in the services directory', '<a class="b b-gold" href="?edit=new">' . icon('plus') . 'New category</a>');
?>
<div class="card">
  <div class="card-head"><div><h2>All categories</h2></div><div class="filters"><input class="input search" placeholder="Search…" data-table-search="#catTable"></div></div>
  <div class="table-wrap"><table class="tbl" id="catTable">
    <thead><tr><th>Code</th><th>Category</th><th>Services</th><th>Order</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($list as $c): ?>
        <tr>
          <td class="mono"><?= e($c['code']) ?></td>
          <td><div style="display:flex;gap:10px;align-items:center"><span class="db-icon-btn" style="pointer-events:none"><?= icon($c['icon'] ?: 'spark') ?></span><div><b><?= e($c['title']) ?></b><br><span class="muted"><?= e($c['slug']) ?></span></div></div></td>
          <td><?= count(lines($c['items'])) ?></td>
          <td><?= (int)$c['sort_order'] ?></td>
          <td><?= $c['is_active'] ? '<span class="pill pill-green">Active</span>' : '<span class="pill pill-neutral">Hidden</span>' ?></td>
          <td class="actions">
            <a class="b b-ghost b-sm" target="_blank" href="<?= e(base_url('service.php?slug=' . $c['slug'])) ?>"><?= icon('eye') ?></a>
            <a class="b b-sm" href="?edit=<?= (int)$c['id'] ?>"><?= icon('edit') ?>Edit</a>
            <form method="post" style="display:inline"><?= csrf_field() ?><button class="b b-danger b-sm" name="delete" value="<?= (int)$c['id'] ?>" data-confirm-submit="Delete “<?= e($c['title']) ?>”?"><?= icon('trash') ?></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php admin_end(); ?>
