<?php
require __DIR__ . '/_layout.php';
require_admin();

$status = get('status'); $qtext = get('q'); $city = get('city');
$where = ['1=1']; $args = [];
if (isset(booking_statuses()[$status])) { $where[] = 'b.status = ?'; $args[] = $status; }
if ($city !== '') { $where[] = 'b.city = ?'; $args[] = $city; }
if ($qtext !== '') { $where[] = '(b.ref LIKE ? OR b.name LIKE ? OR b.phone LIKE ? OR b.service LIKE ?)'; $like = '%' . $qtext . '%'; array_push($args, $like, $like, $like, $like); }

if (get('export') === 'csv') {
    $data = rows('SELECT b.ref, b.created_at, b.name, b.phone, b.email, b.city, b.category, b.service, b.urgency, b.budget, b.deal_amount, b.status, p.name AS provider FROM bookings b LEFT JOIN users p ON p.id = b.provider_id WHERE ' . implode(' AND ', $where) . ' ORDER BY b.id DESC', $args);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="bookings-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Reference', 'Created', 'Customer', 'Phone', 'Email', 'City', 'Category', 'Service', 'Urgency', 'Budget', 'Deal', 'Status', 'Provider']);
    foreach ($data as $r) fputcsv($out, array_values($r));
    exit;
}

$perPage = 25; $page = max(1, (int)get('p', 1));
$total = (int)val('SELECT COUNT(*) FROM bookings b WHERE ' . implode(' AND ', $where), $args);
$list = rows('SELECT b.*, p.name AS provider_name FROM bookings b LEFT JOIN users p ON p.id = b.provider_id WHERE ' . implode(' AND ', $where) . ' ORDER BY b.id DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage), $args);
$counts = [];
foreach (rows('SELECT status, COUNT(*) c FROM bookings GROUP BY status') as $r) $counts[$r['status']] = $r['c'];
$qs = function ($extra) { return '?' . http_build_query(array_merge($_GET, $extra)); };

admin_start('Bookings', $total . ' booking(s)', '<a class="b b-ghost" href="' . e($qs(['export' => 'csv'])) . '">' . icon('upload') . 'Export CSV</a>');
?>
<div class="card">
  <div class="card-head">
    <div class="tabs">
      <a href="bookings.php" class="<?= $status === '' ? 'active' : '' ?>">All <span class="n"><?= array_sum($counts) ?></span></a>
      <?php foreach (booking_statuses() as $k => $s): ?><a href="?status=<?= $k ?>" class="<?= $status === $k ? 'active' : '' ?>"><?= e($s[0]) ?> <span class="n"><?= (int)($counts[$k] ?? 0) ?></span></a><?php endforeach; ?>
    </div>
  </div>
  <div class="card-body" style="padding-bottom:0">
    <form class="filters" method="get">
      <input type="hidden" name="status" value="<?= e($status) ?>">
      <input class="input search" name="q" value="<?= e($qtext) ?>" placeholder="Search reference, name, phone, service…">
      <select class="input" name="city"><option value="">All cities</option><?php foreach (cities() as $c): ?><option<?= $city === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select>
      <button class="b"><?= icon('search') ?>Filter</button>
      <?php if ($qtext || $city): ?><a class="b b-ghost" href="?status=<?= e($status) ?>">Clear</a><?php endif; ?>
    </form>
  </div>
  <?php if (!$list): ?><div class="empty"><?= icon('calendar') ?>No bookings found.</div>
  <?php else: ?>
  <div class="table-wrap" style="margin-top:16px"><table class="tbl">
    <thead><tr><th>Reference</th><th>Customer</th><th>Service</th><th>City</th><th>Budget / Deal</th><th>Provider</th><th>Status</th><th>Created</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($list as $b): ?>
        <tr>
          <td class="mono"><?= e($b['ref']) ?><?= $b['source'] === 'web' ? '<br><span class="muted" style="font-size:.7rem">Website form</span>' : '' ?></td>
          <td><b><?= e($b['name']) ?></b><br><span class="muted"><?= e($b['phone']) ?></span></td>
          <td><?= e($b['service']) ?><br><span class="muted"><?= e($b['category']) ?></span><?= $b['urgency'] !== 'normal' ? ' <span class="pill pill-red">' . e(ucfirst($b['urgency'])) . '</span>' : '' ?></td>
          <td><?= e($b['city']) ?></td>
          <td><?= $b['deal_amount'] ? '<b>' . e(money($b['deal_amount'])) . '</b>' : ($b['budget'] ? e(money($b['budget'])) : '—') ?></td>
          <td><?= e($b['provider_name'] ?: '—') ?></td>
          <td><?= status_pill($b['status']) ?></td>
          <td class="muted"><?= e(date('d M Y', strtotime($b['created_at']))) ?></td>
          <td class="actions"><a class="b b-sm" href="booking-view.php?id=<?= (int)$b['id'] ?>"><?= icon('eye') ?>Manage</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php $pages = (int)ceil($total / $perPage); if ($pages > 1): ?>
    <div class="card-body" style="display:flex;gap:6px;justify-content:center">
      <?php for ($i = 1; $i <= $pages; $i++): ?><a class="b b-sm <?= $i === $page ? '' : 'b-ghost' ?>" href="<?= e($qs(['p' => $i])) ?>"><?= $i ?></a><?php endfor; ?>
    </div>
  <?php endif; endif; ?>
</div>
<?php admin_end(); ?>
