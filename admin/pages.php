<?php
require __DIR__ . '/_layout.php';
require_admin();

if (is_post()) {
    csrf_check();
    if (post('delete')) {
        q('DELETE FROM pages WHERE id = ?', [(int)post('delete')]);
        flash('success', 'Page deleted.');
        redirect('admin/pages.php');
    }
    $id = (int)post('id');
    $slug = slugify(post('slug') ?: post('title'));
    if (post('title') === '' || $slug === '') { flash('error', 'Title is required.'); redirect('admin/pages.php?edit=' . ($id ?: 'new')); }
    if (val('SELECT id FROM pages WHERE slug = ? AND id <> ?', [$slug, $id])) { flash('error', 'Another page already uses that slug.'); redirect('admin/pages.php?edit=' . ($id ?: 'new')); }
    $f = [$slug, post('title'), post('meta_description'), post('content'), post('is_active') ? 1 : 0];
    if ($id) { $f[] = $id; q('UPDATE pages SET slug=?, title=?, meta_description=?, content=?, is_active=? WHERE id=?', $f); }
    else q('INSERT INTO pages (slug, title, meta_description, content, is_active) VALUES (?,?,?,?,?)', $f);
    flash('success', 'Page saved.');
    redirect('admin/pages.php');
}

$edit = get('edit');
if ($edit !== '') {
    $p = $edit === 'new' ? ['id' => 0, 'slug' => '', 'title' => '', 'meta_description' => '', 'content' => '', 'is_active' => 1] : row('SELECT * FROM pages WHERE id = ?', [(int)$edit]);
    if (!$p) redirect('admin/pages.php');
    admin_start($p['id'] ? 'Edit page' : 'New page', 'Simple content pages such as About, Privacy and Terms', '<a class="b b-ghost" href="pages.php">' . icon('chev') . 'Back</a>');
    ?>
    <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <div class="card-body"><div class="form-grid">
        <div class="field"><label>Title</label><input class="input" name="title" value="<?= e($p['title']) ?>" data-slug-source="#pslug" required></div>
        <div class="field"><label>Slug</label><input class="input slug-input" id="pslug" name="slug" value="<?= e($p['slug']) ?>"><div class="hint">page.php?slug=<?= e($p['slug'] ?: '…') ?></div></div>
        <div class="field full"><label>SEO description</label><input class="input" name="meta_description" value="<?= e($p['meta_description']) ?>" maxlength="300"></div>
        <div class="field full"><label>Content (HTML)</label>
          <div style="display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap" id="htmlTools">
            <?php foreach (['h2' => 'Heading', 'p' => 'Paragraph', 'strong' => 'Bold', 'ul' => 'List', 'a' => 'Link'] as $tg => $lb): ?><button type="button" class="b b-ghost b-sm" data-tag="<?= $tg ?>"><?= $lb ?></button><?php endforeach; ?>
          </div>
          <textarea class="input code" id="pageContent" name="content" style="min-height:380px"><?= e($p['content']) ?></textarea></div>
        <div class="field"><label class="switch"><input type="checkbox" name="is_active" value="1"<?= $p['is_active'] ? ' checked' : '' ?>><i></i>Published</label></div>
      </div>
      <div class="form-actions"><a class="b b-ghost" href="pages.php">Cancel</a><button class="b b-gold"><?= icon('check') ?>Save page</button></div></div>
    </form>
    <?php admin_end('<script>
    $("#htmlTools").on("click","button",function(){var t=$(this).data("tag"),ta=document.getElementById("pageContent"),s=ta.selectionStart,e=ta.selectionEnd,sel=ta.value.substring(s,e)||"Text";
    var out=t==="ul"?"<ul>\n  <li>"+sel+"</li>\n</ul>":t==="a"?"<a href=\"#\">"+sel+"</a>":"<"+t+">"+sel+"</"+t+">";
    ta.setRangeText(out,s,e,"end");ta.focus();});</script>');
    exit;
}

$list = rows('SELECT * FROM pages ORDER BY title');
admin_start('Pages', 'About, Privacy, Terms and any extra pages', '<a class="b b-gold" href="?edit=new">' . icon('plus') . 'New page</a>');
?>
<div class="card"><div class="table-wrap"><table class="tbl">
  <thead><tr><th>Title</th><th>URL</th><th>Updated</th><th>Status</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($list as $p): ?>
      <tr><td><b><?= e($p['title']) ?></b></td><td class="mono muted">page.php?slug=<?= e($p['slug']) ?></td><td class="muted"><?= e(time_ago($p['updated_at'])) ?></td>
        <td><?= $p['is_active'] ? '<span class="pill pill-green">Published</span>' : '<span class="pill pill-neutral">Draft</span>' ?></td>
        <td class="actions"><a class="b b-ghost b-sm" target="_blank" href="<?= e(base_url('page.php?slug=' . $p['slug'])) ?>"><?= icon('eye') ?></a><a class="b b-sm" href="?edit=<?= (int)$p['id'] ?>"><?= icon('edit') ?>Edit</a>
          <form method="post" style="display:inline"><?= csrf_field() ?><button class="b b-danger b-sm" name="delete" value="<?= (int)$p['id'] ?>" data-confirm-submit="Delete this page?"><?= icon('trash') ?></button></form></td></tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div>
<?php admin_end(); ?>
