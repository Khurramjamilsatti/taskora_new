<?php
/**
 * Taskora one-click installer.
 * Creates the database (if missing), all tables, and seed content.
 * Usage: open http://localhost/taskora/install.php  (or: php install.php --force)
 * DELETE or rename this file after installing on a live server.
 */
require __DIR__ . '/config.php';

$cli = PHP_SAPI === 'cli';
$force = $cli ? in_array('--force', $argv, true) : (($_POST['confirm'] ?? '') === 'yes');

function out($msg) { global $cli; echo $cli ? $msg . PHP_EOL : '<li>' . htmlspecialchars($msg) . '</li>'; }

if (!$cli && !$force) {
    ?><!doctype html><html><head><meta charset="utf-8"><title>Install Taskora</title>
    <style>body{font-family:Inter,system-ui,sans-serif;background:#04382b;color:#fff;display:grid;place-items:center;min-height:100vh;margin:0}
    .box{background:#0b0d0c;border:1px solid #d4af37;border-radius:18px;padding:40px;max-width:520px}
    h1{margin:0 0 8px;font-weight:600}p{color:#ffffffb3;line-height:1.6}
    button{background:#d4af37;color:#0b0d0c;border:0;padding:14px 26px;border-radius:40px;font-weight:700;cursor:pointer;font-size:15px}
    code{color:#d4af37}</style></head><body><form class="box" method="post">
    <h1>Install Taskora</h1>
    <p>This creates the <code><?= DB_NAME ?></code> database and loads all tables and demo content.
    <b>Existing Taskora tables will be replaced.</b></p>
    <input type="hidden" name="confirm" value="yes"><button>Install now</button></form></body></html><?php
    exit;
}

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    exit('Cannot connect to MySQL: ' . $e->getMessage());
}

if (!$cli) echo '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;max-width:720px;margin:40px auto"><h2>Installing Taskora…</h2><ul>';

$pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE `' . DB_NAME . '`');
out('Database ready: ' . DB_NAME);

$schema = file_get_contents(__DIR__ . '/install/schema.sql');
foreach (array_filter(array_map('trim', explode(';', $schema))) as $stmt) {
    $pdo->exec($stmt);
}
out('Tables created');

$seed = require __DIR__ . '/install/seed.php';

$st = $pdo->prepare('INSERT INTO sections (skey, page, label, tag, title, emphasis, description, data, sort_order) VALUES (?,?,?,?,?,?,?,?,?)');
foreach ($seed['sections'] as $i => $s) {
    $st->execute([$s['key'], $s['page'], $s['label'], $s['tag'], $s['title'], $s['emph'], $s['desc'],
        json_encode($s['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ($i + 1) * 10]);
}
out(count($seed['sections']) . ' content sections');

$st = $pdo->prepare('INSERT INTO categories (code, title, slug, icon, short_desc, description, items, meta_title, meta_description, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)');
require_once __DIR__ . '/includes/icons.php';
foreach ($seed['cats'] as $i => $c) {
    $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(str_replace('&', 'and', $c[0]))), '-');
    $code = 'T' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
    $long = '<p>' . $c[2] . '</p><p>Every ' . strtolower($c[0]) . ' professional on Taskora is verified before activation, quotes transparently before work begins and is rated by customers after every job. Book online, track progress live and pay securely — in Islamabad, Rawalpindi, Lahore, Karachi and beyond.</p>';
    $st->execute([$code, $c[0], $slug, category_icon($c[0]), $c[2], $long, $c[1],
        $c[0] . ' in Pakistan — Book Verified Professionals | Taskora',
        'Book verified ' . strtolower($c[0]) . ' professionals on Taskora. ' . $c[2], ($i + 1) * 10]);
}
out(count($seed['cats']) . ' service categories');

$st = $pdo->prepare('INSERT INTO catalogue_groups (name, count_label, tone, note, body, sort_order) VALUES (?,?,?,?,?,?)');
foreach ($seed['catalogue'] as $i => $c) $st->execute([$c[0], $c[1], $c[2], $c[3], $c[4], ($i + 1) * 10]);
out(count($seed['catalogue']) . ' catalogue groups');

$st = $pdo->prepare('INSERT INTO testimonials (text, author, location, stars, sort_order) VALUES (?,?,?,?,?)');
foreach ($seed['testimonials'] as $i => $t) $st->execute([$t[0], $t[1], $t[2], $t[3], ($i + 1) * 10]);
out(count($seed['testimonials']) . ' testimonials');

$st = $pdo->prepare('INSERT INTO settings (skey, svalue, label, grp, input, sort_order) VALUES (?,?,?,?,?,?)');
foreach ($seed['settings'] as $i => $s) $st->execute([$s[0], $s[1], $s[2], $s[3], $s[4], $i]);
out(count($seed['settings']) . ' settings');

$st = $pdo->prepare('INSERT INTO pages (slug, title, meta_description, content) VALUES (?,?,?,?)');
foreach ($seed['pages'] as $p) $st->execute($p);
out(count($seed['pages']) . ' pages');

$pdo->prepare('INSERT INTO admins (name, email, password, role) VALUES (?,?,?,?)')
    ->execute(['Administrator', 'admin@taskora.digital', password_hash('Admin@123', PASSWORD_DEFAULT), 'superadmin']);
out('Admin user: admin@taskora.digital / Admin@123');

$u = $pdo->prepare('INSERT INTO users (role, name, email, phone, city, password, category, subcategory, experience, service_areas, qualifications, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
$u->execute(['customer', 'Ayesha Khan', 'customer@taskora.digital', '03001234567', 'Islamabad', password_hash('Demo@123', PASSWORD_DEFAULT), null, null, null, null, null, 'active']);
$cust = $pdo->lastInsertId();
$u->execute(['provider', 'Imran Mahmood', 'provider@taskora.digital', '03111234567', 'Islamabad', password_hash('Demo@123', PASSWORD_DEFAULT), 'Home Services', 'AC Repair & Installation', 9, 'Islamabad, Rawalpindi', 'HVAC Diploma, TEVTA', 'active']);
$prov = $pdo->lastInsertId();
out('Demo customer: customer@taskora.digital / Demo@123');
out('Demo provider: provider@taskora.digital / Demo@123');

$b = $pdo->prepare('INSERT INTO bookings (ref, customer_id, provider_id, name, phone, email, city, address, category, service, description, urgency, preferred_date, budget, payment_method, status, deal_amount, source) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
$b->execute(['TSK-DEMO-0001', $cust, $prov, 'Ayesha Khan', '03001234567', 'customer@taskora.digital', 'Islamabad', 'House 12, Street 4, F-7/2', 'Home Services', 'AC Repair & Installation', 'Two split ACs need a full service before summer; one is leaking water.', 'normal', date('Y-m-d', strtotime('+2 days')), 5000, 'Cash', 'quoted', 4400, 'portal']);
$bid = $pdo->lastInsertId();
$pdo->prepare('INSERT INTO booking_offers (booking_id, by_role, amount, message) VALUES (?,?,?,?),(?,?,?,?)')
    ->execute([$bid, 'customer', 5000, 'Budget for both units', $bid, 'provider', 4400, 'Includes gas top-up check and drain-pipe cleaning for both units.']);
$b->execute(['TSK-DEMO-0002', $cust, null, 'Ayesha Khan', '03001234567', 'customer@taskora.digital', 'Islamabad', 'House 12, Street 4, F-7/2', 'Home Services', 'House & Deep Cleaning', 'Deep cleaning for a 3-bed portion including kitchen and washrooms.', 'urgent', date('Y-m-d', strtotime('+4 days')), 9000, 'Easypaisa', 'requested', null, 'portal']);
$pdo->prepare('INSERT INTO notifications (user_id, title, body, link) VALUES (?,?,?,?)')
    ->execute([$cust, 'Quotation received', 'Imran Mahmood shared a quotation of PKR 4,400 for TSK-DEMO-0001.', 'account/booking.php?id=' . $bid]);
out('Demo bookings created');

@mkdir(UPLOAD_DIR, 0775, true);

if ($cli) {
    echo "Done.\n";
} else {
    echo '</ul><p style="padding:16px;background:#edf2ee;border-radius:10px"><b>Installation complete.</b> '
       . '<a href="index.php">Open website</a> · <a href="admin/login.php">Open admin</a><br>'
       . '<small>For security, delete <code>install.php</code> on a live server.</small></p></body>';
}
