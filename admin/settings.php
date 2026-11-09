<?php
require __DIR__ . '/_layout.php';
require_admin();

$groups = ['general' => 'General & branding', 'contact' => 'Contact details', 'social' => 'Social & app links', 'seo' => 'SEO & analytics', 'footer' => 'Footer'];
$all = rows('SELECT * FROM settings ORDER BY grp, sort_order');

if (is_post()) {
    csrf_check();
    foreach ($all as $s) {
        $k = $s['skey'];
        if ($s['input'] === 'image') {
            if (post('remove_' . $k)) { q('UPDATE settings SET svalue = ? WHERE skey = ?', ['', $k]); continue; }
            $path = handle_upload('file_' . $k, $k);
            if ($path) q('UPDATE settings SET svalue = ? WHERE skey = ?', [$path, $k]);
            continue;
        }
        if (!array_key_exists($k, $_POST)) continue;
        $v = post($k);
        if ($k === 'whatsapp') $v = preg_replace('/\D/', '', $v);
        if ($k === 'email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) { flash('error', 'The email address is not valid — it was not saved.'); continue; }
        q('UPDATE settings SET svalue = ? WHERE skey = ?', [$v, $k]);
    }
    flash('success', 'Settings saved.');
    redirect('admin/settings.php' . (post('_tab') ? '#' . post('_tab') : ''));
}

admin_start('Settings & SEO', 'Branding, contact details, social links and search engine settings');
?>
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <?php foreach ($groups as $gk => $gl): ?>
    <div class="card" id="<?= $gk ?>">
      <div class="card-head"><div><h2><?= e($gl) ?></h2></div></div>
      <div class="card-body"><div class="form-grid">
        <?php foreach ($all as $s): if ($s['grp'] !== $gk) continue; $k = $s['skey']; ?>
          <div class="field<?= $s['input'] === 'textarea' ? ' full' : '' ?>">
            <label for="s_<?= e($k) ?>"><?= e($s['label'] ?: $k) ?></label>
            <?php if ($s['input'] === 'textarea'): ?>
              <textarea class="input" id="s_<?= e($k) ?>" name="<?= e($k) ?>"><?= e($s['svalue']) ?></textarea>
            <?php elseif ($s['input'] === 'image'): ?>
              <img class="thumb" id="pv_<?= e($k) ?>" src="<?= $s['svalue'] ? e(base_url($s['svalue'])) : '' ?>" alt="" <?= $s['svalue'] ? '' : 'style="display:none"' ?>>
              <input class="input" type="file" name="file_<?= e($k) ?>" accept="image/*" data-preview="#pv_<?= e($k) ?>">
              <?php if ($s['svalue']): ?><label class="check-row" style="margin-top:8px;text-transform:none;letter-spacing:0"><input type="checkbox" name="remove_<?= e($k) ?>" value="1"> Remove and use the built-in default</label><?php endif; ?>
            <?php else: ?>
              <input class="input" id="s_<?= e($k) ?>" name="<?= e($k) ?>" value="<?= e($s['svalue']) ?>">
            <?php endif; ?>
            <?php if ($k === 'map_embed'): ?><div class="hint">Google Maps → Share → Embed a map → copy only the <code>src="…"</code> URL.</div><?php endif; ?>
            <?php if ($k === 'meta_description'): ?><div class="hint">Aim for 140–160 characters. Currently <span id="mdLen"><?= mb_strlen($s['svalue']) ?></span>.</div><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div></div>
    </div>
  <?php endforeach; ?>
  <div class="card"><div class="card-body form-actions" style="padding-top:22px"><button class="b b-gold"><?= icon('check') ?>Save all settings</button></div></div>
</form>
<?php admin_end('<script>$("#s_meta_description").on("input",function(){$("#mdLen").text(this.value.length)});</script>'); ?>
