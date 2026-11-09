<?php
require __DIR__ . '/_layout.php';
require_admin();

$types = ['company' => 'Company', 'feedback' => 'Feedback', 'complaint' => 'Complaint', 'refund' => 'Refund', 'insurance' => 'Insurance', 'contact' => 'Contact'];
$statuses = ['new' => ['New', 'gold'], 'in_review' => ['In review', 'info'], 'resolved' => ['Resolved', 'green']];

if (is_post()) {
    csrf_check();
    $id = (int)post('id');
    if (post('delete')) {
        q('DELETE FROM form_submissions WHERE id = ?', [$id]);
        flash('success', 'Submission deleted.');
        redirect('admin/submissions.php');
    }
    $st = isset($statuses[post('status')]) ? post('status') : 'new';
    q('UPDATE form_submissions SET status = ?, admin_note = ? WHERE id = ?', [$st, post('admin_note'), $id]);
    $sub = row('SELECT user_id, ref FROM form_submissions WHERE id = ?', [$id]);
    if ($sub && $sub['user_id']) notify($sub['user_id'], 'Update on ' . $sub['ref'], 'Status: ' . $statuses[$st][0], '');
    flash('success', 'Submission updated.');
    redirect('admin/submissions.php?view=' . $id);
}

if ($vid = (int)get('view')) {
    $s = row('SELECT * FROM form_submissions WHERE id = ?', [$vid]);
    if (!$s) redirect('admin/submissions.php');
    if ($s['status'] === 'new') { q("UPDATE form_submissions SET status = 'in_review' WHERE id = ?", [$vid]); $s['status'] = 'in_review'; }
    $data = json_decode($s['data'], true) ?: [];
    admin_start(($types[$s['form_type']] ?? ucfirst($s['form_type'])) . ' · ' . $s['ref'], 'Received ' . date('d M Y, g:i A', strtotime($s['created_at'])), '<a class="b b-ghost" href="submissions.php">' . icon('chev') . 'Back</a>');
    ?>
    <div class="row-2">
      <div class="card"><div class="card-head"><div><h2>Submitted details</h2></div><span class="pill pill-<?= $statuses[$s['status']][1] ?>"><?= $statuses[$s['status']][0] ?></span></div>
        <div class="card-body"><div class="dl">
          <?php foreach ($data as $k => $v): if ($v === '' || $k === 'agree') continue; $long = mb_strlen($v) > 60; ?>
            <div class="<?= $long ? 'full' : '' ?>"><small><?= e(ucwords(str_replace('_', ' ', $k))) ?></small><strong style="<?= $long ? 'font-weight:400' : '' ?>"><?= $k === 'rating' ? '<span style="color:var(--gold)">' . str_repeat('★', (int)$v) . '</span>' : nl($v) ?></strong></div>
          <?php endforeach; ?>
          <div><small>IP address</small><strong class="mono"><?= e($s['ip']) ?></strong></div>
        </div></div></div>
      <div>
        <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
          <div class="card-head"><div><h2>Follow-up</h2></div></div>
          <div class="card-body">
            <div class="field"><label>Status</label><select class="input" name="status"><?php foreach ($statuses as $k => $l): ?><option value="<?= $k ?>"<?= $s['status'] === $k ? ' selected' : '' ?>><?= $l[0] ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Internal note</label><textarea class="input" name="admin_note"><?= e($s['admin_note']) ?></textarea></div>
            <div class="form-actions">
              <?php if ($s['email']): ?><a class="b b-ghost" href="mailto:<?= e($s['email']) ?>?subject=<?= rawurlencode('Re: ' . $s['ref']) ?>"><?= icon('mail') ?>Reply</a><?php endif; ?>
              <?php if ($s['phone']): ?><a class="b b-ghost" href="https://wa.me/<?= e(preg_replace('/^0/', '92', preg_replace('/\D/', '', $s['phone']))) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?>WhatsApp</a><?php endif; ?>
              <button class="b b-gold"><?= icon('check') ?>Save</button>
            </div>
          </div>
        </form>
        <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
          <div class="card-body" style="display:flex;justify-content:space-between;align-items:center"><span class="muted">Delete permanently</span><button class="b b-danger" name="delete" value="1" data-confirm-submit="Delete this submission?"><?= icon('trash') ?>Delete</button></div>
        </form>
      </div>
    </div>
    <?php admin_end(); exit;
}

$type = get('type'); $st = get('status');
$where = ['1=1']; $args = [];
if (isset($types[$type])) { $where[] = 'form_type = ?'; $args[] = $type; }
if (isset($statuses[$st])) { $where[] = 'status = ?'; $args[] = $st; }
$list = rows('SELECT * FROM form_submissions WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC LIMIT 300', $args);
$counts = [];
foreach (rows('SELECT form_type, COUNT(*) c FROM form_submissions GROUP BY form_type') as $r) $counts[$r['form_type']] = $r['c'];

admin_start('Form submissions', 'Company, feedback, complaint, refund, insurance and contact forms');
?>
<div class="card">
  <div class="card-head">
    <div class="tabs">
      <a href="submissions.php" class="<?= $type === '' ? 'active' : '' ?>">All <span class="n"><?= array_sum($counts) ?></span></a>
      <?php foreach ($types as $k => $l): ?><a href="?type=<?= $k ?>" class="<?= $type === $k ? 'active' : '' ?>"><?= $l ?> <span class="n"><?= (int)($counts[$k] ?? 0) ?></span></a><?php endforeach; ?>
    </div>
    <div class="filters">
      <select class="input" onchange="location.href='?type=<?= e($type) ?>&status='+this.value"><option value="">Any status</option><?php foreach ($statuses as $k => $l): ?><option value="<?= $k ?>"<?= $st === $k ? ' selected' : '' ?>><?= $l[0] ?></option><?php endforeach; ?></select>
      <input class="input search" placeholder="Search…" data-table-search="#subTable">
    </div>
  </div>
  <?php if (!$list): ?><div class="empty"><?= icon('inbox') ?>No submissions yet.</div><?php else: ?>
  <div class="table-wrap"><table class="tbl" id="subTable">
    <thead><tr><th>Reference</th><th>Type</th><th>From</th><th>Summary</th><th>Status</th><th>Received</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($list as $s): $d = json_decode($s['data'], true) ?: []; $sum = $d['subject'] ?? ($d['complaint_type'] ?? ($d['services_required'] ?? ($d['reason'] ?? ($d['comments'] ?? ($d['incident'] ?? ''))))); ?>
        <tr<?= $s['status'] === 'new' ? ' style="font-weight:600"' : '' ?>>
          <td class="mono"><?= e($s['ref']) ?></td>
          <td><?= e($types[$s['form_type']] ?? $s['form_type']) ?></td>
          <td><?= e($s['name']) ?><br><span class="muted" style="font-weight:400"><?= e($s['email'] ?: $s['phone']) ?></span></td>
          <td class="muted" style="max-width:280px;font-weight:400"><?= e(mb_strimwidth((string)$sum, 0, 70, '…')) ?></td>
          <td><span class="pill pill-<?= $statuses[$s['status']][1] ?>"><?= $statuses[$s['status']][0] ?></span></td>
          <td class="muted"><?= e(time_ago($s['created_at'])) ?></td>
          <td class="actions"><a class="b b-sm" href="?view=<?= (int)$s['id'] ?>"><?= icon('eye') ?>Open</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div><?php endif; ?>
</div>
<?php admin_end(); ?>
