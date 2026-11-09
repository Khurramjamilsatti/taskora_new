<?php
require __DIR__ . '/_layout.php';
require_once dirname(__DIR__) . '/includes/pro_registration.php';
require_admin();
ensure_pro_tables();

$statuses = ['submitted' => ['Submitted', 'gold'], 'under_review' => ['Under review', 'info'], 'approved' => ['Approved', 'green'], 'rejected' => ['Rejected', 'red']];
function app_pill($s, $statuses) { $x = $statuses[$s] ?? [$s, 'neutral']; return '<span class="pill pill-' . $x[1] . '">' . e($x[0]) . '</span>'; }

if (is_post()) {
    csrf_check();
    $app = row('SELECT * FROM provider_applications WHERE id = ?', [(int)post('id')]);
    if ($app && isset($statuses[post('status')])) {
        $new = post('status');
        q('UPDATE provider_applications SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?', [$new, post('admin_notes'), $app['id']]);
        if ($new !== $app['status'] && $app['user_id']) {
            if ($new === 'approved') {
                q("UPDATE users SET status = 'active' WHERE id = ?", [$app['user_id']]);
                notify($app['user_id'], 'Your profile is verified 🎉', 'Application ' . $app['ref'] . ' is approved. You can now accept booking requests on Taskora.', 'account/requests.php');
            } elseif ($new === 'rejected') {
                q("UPDATE users SET status = 'suspended' WHERE id = ?", [$app['user_id']]);
                notify($app['user_id'], 'Application ' . $app['ref'] . ' not approved', 'Contact Taskora support if you think this is a mistake.', 'account/index.php');
            } elseif ($new === 'under_review') {
                notify($app['user_id'], 'Application ' . $app['ref'] . ' is under review', 'We are checking your documents and references.', 'account/index.php');
            }
        }
        flash('success', 'Application ' . $app['ref'] . ' updated.');
        redirect('admin/applications.php?id=' . $app['id']);
    }
    redirect('admin/applications.php');
}

/* ---------- Detail ---------- */
if ($id = (int)get('id')) {
    $a = row('SELECT a.*, u.name AS user_name, u.status AS user_status FROM provider_applications a LEFT JOIN users u ON u.id = a.user_id WHERE a.id = ?', [$id]);
    if (!$a) { flash('error', 'Application not found.'); redirect('admin/applications.php'); }
    $data = json_decode($a['data'], true) ?: [];
    $docs = json_decode($a['documents'] ?: '{}', true) ?: [];
    $fees = json_decode($a['fees'] ?: '{}', true) ?: [];
    $ap = $data['applicant'] ?? [];
    $ct = $data['contact'] ?? [];
    $labels = ['name' => 'Full name', 'father_name' => "Father's / husband's name", 'cnic' => 'CNIC', 'dob' => 'Date of birth', 'experience_years' => 'Experience (years)',
        'qualification' => 'Qualification', 'business_name' => 'Business name', 'secp' => 'SECP / deed no.', 'ntn' => 'NTN', 'strn' => 'STRN', 'staff' => 'Staff',
        'licence' => 'Licence no.', 'signatory' => 'Authorised signatory', 'signatory_cnic' => 'Signatory CNIC'];
    admin_start('Application ' . $a['ref'], $a['provider_type'] . ' · ' . $a['category'] . ' · Tier ' . $a['tier'],
        '<a class="b b-ghost" href="applications.php">' . icon('list') . 'All applications</a>');
    ?>
<div class="app-grid">
  <div>
    <div class="card" style="margin-bottom:22px">
      <div class="card-head"><h3>Applicant</h3><?= app_pill($a['status'], $statuses) ?></div>
      <div class="dl" style="padding:0 22px 10px">
        <?php foreach ($ap as $k => $v): if ($v === '' || $v === null) continue; ?>
          <div><small><?= e($labels[$k] ?? $k) ?></small><strong><?= e($v) ?></strong></div>
        <?php endforeach; ?>
        <div><small>Mobile</small><strong><?= e($ct['mobile'] ?? '') ?></strong></div>
        <div><small>Email</small><strong><?= e($ct['email'] ?? '') ?></strong></div>
        <div class="full"><small>Address</small><strong><?= nl($ct['address'] ?? '') ?></strong></div>
        <?php foreach (($ct['references'] ?? []) as $i => $r): ?><div><small>Reference <?= $i + 1 ?></small><strong><?= e($r) ?></strong></div><?php endforeach; ?>
        <div><small>City</small><strong><?= e($a['city']) ?><?= $a['waitlist'] ? ' (waitlist)' : '' ?></strong></div>
        <div><small>Areas covered</small><strong><?= e(($data['areas'] ?? '') ?: '—') ?></strong></div>
        <div class="full"><small>Services (<?= count($data['services'] ?? []) ?>)</small><strong><?= e(implode(', ', array_column($data['services'] ?? [], 'name'))) ?></strong></div>
        <?php if (!empty($data['fee_row']['licence'])): ?><div class="full"><small>Category requirements</small><strong><?= e($data['fee_row']['licence']) ?></strong></div><?php endif; ?>
      </div>
    </div>

    <div class="card" style="margin-bottom:22px">
      <div class="card-head"><h3>Documents</h3><span class="muted"><?= count($docs) ?> type(s)</span></div>
      <?php if (!$docs): ?><div class="empty"><?= icon('doc') ?>No documents uploaded.</div><?php else: ?>
      <div class="table-wrap"><table class="tbl">
        <thead><tr><th>Document</th><th>Files</th></tr></thead>
        <tbody>
        <?php foreach ($docs as $docId => $doc): ?>
          <tr><td><b><?= e($doc['label'] ?? $docId) ?></b></td><td>
            <?php foreach ($doc['files'] as $f): ?>
              <a class="b b-ghost b-sm" style="margin:2px" target="_blank" href="application-file.php?id=<?= (int)$a['id'] ?>&amp;f=<?= e(rawurlencode($f['file'])) ?>"><?= icon('doc') ?><?= e($f['name']) ?> <span class="muted">(<?= e(pro_mb($f['size'])) ?>)</span></a>
            <?php endforeach; ?>
          </td></tr>
        <?php endforeach; ?>
        </tbody>
      </table></div><?php endif; ?>
    </div>

    <div class="card">
      <div class="card-head"><h3>Checks to run for tier <?= e($a['tier']) ?></h3></div>
      <div style="padding:0 22px 20px;display:flex;flex-wrap:wrap;gap:6px">
        <?php foreach (pro_checks($a['tier']) as $c): ?><span class="pill pill-<?= $c['conditional'] ? 'gold' : 'neutral' ?>" title="<?= e($c['what']) ?>"><?= e($c['name']) ?><?= $c['conditional'] ? ' (if applicable)' : '' ?></span><?php endforeach; ?>
      </div>
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:22px">
      <div class="card-head"><h3>Review</h3></div>
      <form method="post" style="padding:0 22px 22px"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
        <div class="field"><label>Status</label><select class="input" name="status"><?php foreach ($statuses as $k => $s): ?><option value="<?= e($k) ?>"<?= $a['status'] === $k ? ' selected' : '' ?>><?= e($s[0]) ?></option><?php endforeach; ?></select>
          <p class="hint">Approved activates the provider account. Rejected suspends it.</p></div>
        <div class="field"><label>Internal notes</label><textarea class="input" name="admin_notes"><?= e($a['admin_notes']) ?></textarea></div>
        <button class="b" type="submit"><?= icon('check') ?>Save</button>
      </form>
    </div>
    <div class="card" style="margin-bottom:22px">
      <div class="card-head"><h3>Fees (first year)</h3></div>
      <div class="dl" style="padding:0 22px 10px;grid-template-columns:1fr">
        <?php if (!empty($fees['partner'])): ?>
          <div><small>Partner institution</small><strong>No fees: agreed in a partnership agreement</strong></div>
        <?php else: ?>
          <div><small>Application fee</small><strong><?= money($fees['app'] ?? 0) ?><?= ($fees['app'] ?? 0) != ($fees['app_list'] ?? 0) ? ' <span class="muted"><s>' . money($fees['app_list']) . '</s></span>' : '' ?></strong></div>
          <div><small>Registration fee</small><strong><?= money($fees['reg'] ?? 0) ?><?= ($fees['reg'] ?? 0) != ($fees['reg_list'] ?? 0) ? ' <span class="muted"><s>' . money($fees['reg_list']) . '</s></span>' : '' ?></strong></div>
          <div><small>Membership (<?= e($fees['billing'] ?? '') ?><?= !empty($fees['free_months']) ? ', ' . (int)$fees['free_months'] . ' months free' : '' ?>)</small><strong><?= money($fees['membership'] ?? 0) ?></strong></div>
          <div><small>Total</small><strong><?= money($fees['total'] ?? 0) ?></strong></div>
          <div><small>Founding-partner offer</small><strong><?= !empty($fees['founding']) ? 'Yes' : 'No' ?></strong></div>
        <?php endif; ?>
        <div><small>Preferred payment</small><strong><?= e($data['preferred_payment'] ?? '') ?></strong></div>
        <div><small>Payout account</small><strong><?= e($data['payout_account'] ?? '') ?: '—' ?></strong></div>
      </div>
    </div>
    <div class="card">
      <div class="card-head"><h3>Record</h3></div>
      <div class="dl" style="padding:0 22px 10px;grid-template-columns:1fr">
        <div><small>Submitted</small><strong><?= e(date('d M Y, H:i', strtotime($a['created_at']))) ?></strong></div>
        <div><small>Account</small><strong><?= $a['user_id'] ? e($a['user_name']) . ' · ' . e($a['user_status']) : '—' ?></strong></div>
        <div><small>IP address</small><strong><?= e($data['ip'] ?? '') ?></strong></div>
      </div>
    </div>
  </div>
</div>
<?php
    admin_end();
    exit;
}

/* ---------- List ---------- */
$st = get('status');
$where = '1=1'; $args = [];
if (isset($statuses[$st])) { $where = 'a.status = ?'; $args[] = $st; }
$list = rows('SELECT a.id, a.ref, a.provider_type, a.category, a.tier, a.city, a.waitlist, a.status, a.created_at, a.data, u.name
    FROM provider_applications a LEFT JOIN users u ON u.id = a.user_id WHERE ' . $where . ' ORDER BY a.id DESC LIMIT 500', $args);
$counts = [];
foreach (rows('SELECT status, COUNT(*) n FROM provider_applications GROUP BY status') as $r) $counts[$r['status']] = $r['n'];

admin_start('Pro applications', count($list) . ' application(s)');
?>
<div class="card">
  <div class="card-head">
    <div class="tabs">
      <a href="applications.php" class="<?= !$st ? 'active' : '' ?>">All</a>
      <?php foreach ($statuses as $k => $s): ?><a href="?status=<?= e($k) ?>" class="<?= $st === $k ? 'active' : '' ?>"><?= e($s[0]) ?><span class="n"><?= (int)($counts[$k] ?? 0) ?></span></a><?php endforeach; ?>
    </div>
    <div class="filters"><input class="input search" placeholder="Search ref, name, category…" data-table-search="#appTable"></div>
  </div>
  <?php if (!$list): ?><div class="empty"><?= icon('inbox') ?>No applications yet.</div><?php else: ?>
  <div class="table-wrap"><table class="tbl" id="appTable">
    <thead><tr><th>Ref</th><th>Applicant</th><th>Type</th><th>Category</th><th>Tier</th><th>City</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $a): $dd = json_decode($a['data'], true) ?: []; ?>
      <tr>
        <td><b><?= e($a['ref']) ?></b></td>
        <td><?= e($a['name'] ?: '—') ?><br><span class="muted"><?= e($dd['contact']['mobile'] ?? '') ?></span></td>
        <td><?= e($a['provider_type']) ?></td>
        <td><?= e($a['category']) ?><br><span class="muted"><?= count($dd['services'] ?? []) ?> service(s)</span></td>
        <td><span class="pill pill-neutral"><?= e($a['tier']) ?></span></td>
        <td><?= e($a['city']) ?><?= $a['waitlist'] ? '<br><span class="muted">waitlist</span>' : '' ?></td>
        <td><?= e(time_ago($a['created_at'])) ?></td>
        <td><?= app_pill($a['status'], $statuses) ?></td>
        <td class="actions"><a class="b b-sm" href="?id=<?= (int)$a['id'] ?>"><?= icon('eye') ?>Open</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div><?php endif; ?>
</div>
<?php admin_end(); ?>
