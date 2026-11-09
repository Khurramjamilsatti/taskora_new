<?php
/**
 * Generic section editor.
 * - Scalars          → text inputs
 * - Lists of strings → textarea (one item per line)
 * - Lists of objects → repeater table (add / remove / reorder rows)
 * - Advanced tab     → raw JSON for power users
 */
require __DIR__ . '/_layout.php';
require_admin();

$s = row('SELECT * FROM sections WHERE id = ?', [(int)get('id')]);
if (!$s) { flash('error', 'Section not found.'); redirect('admin/sections.php'); }
$data = json_decode($s['data'] ?: '{}', true) ?: [];

function is_list_of_scalars($v) { if (!is_array($v) || !$v) return is_array($v); foreach ($v as $k => $i) { if (!is_int($k) || is_array($i)) return false; } return true; }
function is_list_of_objects($v) { if (!is_array($v) || !$v) return false; foreach ($v as $k => $i) { if (!is_int($k) || !is_array($i) || isset($i[0])) return false; } return true; }
function cast_like($new, $old)
{
    if (is_int($old) && is_numeric($new)) return (int)$new;
    if (is_float($old) && is_numeric($new)) return (float)$new;
    return $new;
}
function nice($k) { return ucwords(str_replace('_', ' ', $k)); }

if (is_post()) {
    csrf_check();
    $mode = post('mode');
    if ($mode === 'json') {
        $raw = (string)post('raw_json');
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            flash('error', 'Invalid JSON: ' . json_last_error_msg() . '. Nothing was saved.');
            redirect('admin/section-edit.php?id=' . $s['id'] . '#advanced');
        }
        $data = $decoded;
    } else {
        $new = [];
        foreach ($data as $key => $val) {
            if (is_list_of_objects($val)) {
                $rowsIn = $_POST['rep'][$key] ?? [];
                $cols = array_keys($val[0]);
                $out = [];
                foreach ($rowsIn as $r) {
                    if (!is_array($r)) continue;
                    $obj = []; $empty = true;
                    foreach ($cols as $c) {
                        $v = trim((string)($r[$c] ?? ''));
                        if ($v !== '') $empty = false;
                        $obj[$c] = cast_like($v, $val[0][$c]);
                    }
                    if (!$empty) $out[] = $obj;
                }
                $new[$key] = $out ?: $val; // never leave a repeater with zero rows
            } elseif (is_list_of_scalars($val)) {
                $sample = $val[0] ?? '';
                $new[$key] = array_map(function ($l) use ($sample) { return cast_like($l, $sample); }, lines($_POST['list'][$key] ?? ''));
            } else {
                $new[$key] = cast_like(trim((string)($_POST['val'][$key] ?? '')), $val);
            }
        }
        $data = $new;
        q('UPDATE sections SET tag = ?, title = ?, emphasis = ?, description = ?, is_active = ? WHERE id = ?', [
            post('tag'), str_replace("\r\n", "\n", post('title')), post('emphasis'), post('description'), post('is_active') ? 1 : 0, $s['id'],
        ]);
    }
    q('UPDATE sections SET data = ? WHERE id = ?', [json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $s['id']]);
    flash('success', '“' . $s['label'] . '” saved. Changes are live on the website.');
    redirect('admin/section-edit.php?id=' . $s['id']);
}

$preview = base_url($s['page'] === 'home' ? 'index.php' : 'catalogue.php');
admin_start('Edit: ' . $s['label'], 'Section key: ' . $s['skey'],
    '<a class="b b-ghost" href="sections.php">' . icon('chev') . 'All sections</a><a class="b b-ghost" target="_blank" href="' . e($preview) . '">' . icon('eye') . 'Preview</a>');
?>
<form method="post" class="card">
  <?= csrf_field() ?>
  <input type="hidden" name="mode" value="fields">
  <div class="card-head"><div><h2>Heading &amp; text</h2><p>Use a new line in the title to create a line break. “Emphasis” is the phrase shown in gold italics.</p></div>
    <label class="switch"><input type="checkbox" name="is_active" value="1"<?= $s['is_active'] ? ' checked' : '' ?>><i></i>Visible on site</label></div>
  <div class="card-body">
    <div class="form-grid">
      <div class="field"><label>Eyebrow tag</label><input class="input" name="tag" value="<?= e($s['tag']) ?>"></div>
      <div class="field"><label>Emphasis phrase</label><input class="input" name="emphasis" value="<?= e($s['emphasis']) ?>"><div class="hint">Must match the end of the title exactly.</div></div>
      <div class="field full"><label>Title</label><textarea class="input" name="title" style="min-height:90px"><?= e($s['title']) ?></textarea></div>
      <div class="field full"><label>Description</label><textarea class="input" name="description"><?= e($s['description']) ?></textarea></div>
    </div>
  </div>

  <?php if ($data): ?>
  <div class="card-head" style="border-top:1px solid var(--db-border)"><div><h2>Section content</h2><p>Lists, cards, numbers and other items in this section</p></div></div>
  <div class="card-body">
    <?php foreach ($data as $key => $val): ?>
      <?php if (is_list_of_objects($val)): $cols = array_keys($val[0]); ?>
        <div class="json-block">
          <label class="label"><span><?= e(nice($key)) ?> <span class="muted">(<?= count($val) ?> items)</span></span><button type="button" class="b b-sm b-ghost rep-add" data-target="rep-<?= e($key) ?>"><?= icon('plus') ?>Add row</button></label>
          <div class="table-wrap">
            <table class="rep-table" id="rep-<?= e($key) ?>" data-key="<?= e($key) ?>">
              <thead><tr><th></th><?php foreach ($cols as $c): ?><th><?= e(nice($c)) ?></th><?php endforeach; ?><th></th></tr></thead>
              <tbody>
                <?php foreach ($val as $i => $r): ?>
                  <tr>
                    <td class="rep-handle"><button type="button" class="b b-sm b-ghost rep-up" title="Move up">↑</button></td>
                    <?php foreach ($cols as $c): $v = isset($r[$c]) ? (is_array($r[$c]) ? implode(', ', $r[$c]) : $r[$c]) : ''; $long = mb_strlen((string)$v) > 60 || in_array($c, ['description', 'text', 'items', 'features'], true); ?>
                      <td>
                        <?php if ($c === 'color'): ?>
                          <div style="display:flex;gap:6px;align-items:center"><input type="color" class="color-pick" value="<?= e($v) ?>" style="width:38px;height:38px;border:0;background:none;padding:0"><input class="input" name="rep[<?= e($key) ?>][<?= $i ?>][<?= e($c) ?>]" value="<?= e($v) ?>" style="width:100px"></div>
                        <?php elseif ($long): ?>
                          <textarea class="input" name="rep[<?= e($key) ?>][<?= $i ?>][<?= e($c) ?>]" rows="2"><?= e($v) ?></textarea>
                        <?php else: ?>
                          <input class="input" name="rep[<?= e($key) ?>][<?= $i ?>][<?= e($c) ?>]" value="<?= e($v) ?>">
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
                    <td><button type="button" class="b b-sm b-danger rep-del" title="Remove"><?= icon('trash') ?></button></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if (in_array('items', $cols, true) || in_array('features', $cols, true)): ?><div class="hint">Separate multiple items with commas.</div><?php endif; ?>
        </div>
      <?php elseif (is_list_of_scalars($val)): ?>
        <div class="field">
          <label><?= e(nice($key)) ?> <span class="muted">— one per line (<?= count($val) ?>)</span></label>
          <textarea class="input" name="list[<?= e($key) ?>]" rows="<?= min(14, max(4, count($val) + 1)) ?>"><?= e(implode("\n", $val)) ?></textarea>
        </div>
      <?php else: ?>
        <div class="field">
          <label><?= e(nice($key)) ?></label>
          <?php if (mb_strlen((string)$val) > 80): ?>
            <textarea class="input" name="val[<?= e($key) ?>]"><?= e($val) ?></textarea>
          <?php else: ?>
            <input class="input" name="val[<?= e($key) ?>]" value="<?= e($val) ?>">
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="card-body" style="border-top:1px solid var(--db-border)">
    <div class="form-actions"><a class="b b-ghost" href="sections.php">Cancel</a><button class="b b-gold" type="submit"><?= icon('check') ?>Save section</button></div>
  </div>
</form>

<form method="post" class="card" id="advanced">
  <?= csrf_field() ?>
  <input type="hidden" name="mode" value="json">
  <div class="card-head"><div><h2>Advanced: raw JSON</h2><p>For developers. Invalid JSON is rejected and nothing is saved.</p></div><button type="button" class="b b-ghost b-sm" onclick="document.getElementById('rawBox').hidden=!document.getElementById('rawBox').hidden">Show / hide</button></div>
  <div class="card-body" id="rawBox" hidden>
    <textarea class="input code" name="raw_json" spellcheck="false"><?= e(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></textarea>
    <div class="form-actions"><button class="b b-danger" type="submit" data-confirm-submit="Replace this section's content with the JSON above?"><?= icon('code') ?>Save JSON</button></div>
  </div>
</form>
<?php admin_end(); ?>
