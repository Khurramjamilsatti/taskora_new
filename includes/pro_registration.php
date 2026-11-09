<?php
/**
 * Professional (provider) registration: fee tiers, required documents, checks.
 * Data lives in includes/data/pro_registration.json (tiers, fee schedule, checks, services).
 *
 * The browser wizard (account/_register_pro.php) mirrors pro_docs() and pro_fees() for display;
 * the server always recalculates them on submit, so keep both in sync when you change a rule.
 */

function pro_data()
{
    static $d = null;
    if ($d === null) {
        $d = json_decode(file_get_contents(__DIR__ . '/data/pro_registration.json'), true) ?: [];
    }
    return $d;
}

/** "01 Home Services" -> "Home Services" */
function pro_clean_cat($c) { return preg_replace('/^\d+\s+/', '', (string)$c); }

/** Provider types: value => label. */
function pro_types()
{
    return [
        'Individual'           => 'Individual',
        'Firm'                 => 'Firm / company',
        'Vendor - Retailer'    => 'Store retailer',
        'Vendor - Distributor' => 'Store distributor',
        'Partner Institution'  => 'Bank / insurer partner',
    ];
}

/** Cities open at launch; other cities join a waitlist. */
function pro_launch_cities() { return ['Islamabad', 'Rawalpindi']; }

/** Site cities (Admin → Settings) plus the national list. */
function pro_cities()
{
    return array_values(array_unique(array_merge(cities(), pro_data()['cities'] ?? [])));
}

/** Categories that send people into homes or to children/patients: police certificate rules. */
function pro_inhome_cats()
{
    return ['Home Services', 'Construction & Renovation', 'Tutors & Education', 'Healthcare Services', 'Cleaning & Laundry',
        'Beauty & Wellness', 'Security Services', 'Logistics & Transport', 'Errands & Personal Assistance',
        'Facility Management', 'Automotive', 'Solar & Energy'];
}

/** clean name => ['key' => "01 …", 'label' => …, 'types' => [type => feeRow]] */
function pro_categories()
{
    static $out = null;
    if ($out !== null) return $out;
    $d = pro_data();
    $out = [];
    foreach ($d['services'] as $s) {
        $c = pro_clean_cat($s['cat']);
        if (!isset($out[$c])) $out[$c] = ['key' => $s['cat'], 'label' => $c, 'types' => []];
    }
    foreach ($d['fees'] as $f) {
        if (!isset($out[$f['cat']])) $out[$f['cat']] = ['key' => $f['cat'], 'label' => $f['cat'], 'types' => []];
        $out[$f['cat']]['types'][$f['type']] = $f;
    }
    // Categories without a fee row: derive Individual / Firm fees from the tiers.
    foreach ($out as $name => &$ci) {
        if ($ci['types']) continue;
        $t = 'T2';
        foreach ($d['services'] as $s) if ($s['cat'] === $ci['key']) { $t = $s['tier']; break; }
        foreach ([['Individual', $t], ['Firm', 'T4']] as $p) {
            $T = $d['tiers'][$p[1]];
            $ci['types'][$p[0]] = ['cat' => $name, 'type' => $p[0], 'tier' => $p[1], 'app' => $T['app'], 'reg' => $T['reg'],
                'annual' => $T['annual'], 'monthly' => $T['monthly'], 'std' => $T['std'], 'launch' => $T['launch'],
                'lic' => 'Requirements confirmed at review', 'who' => $T['desc']];
        }
    }
    unset($ci);
    return $out;
}

/** Can this provider type offer this service? (B = both, I = individual, F = firm) */
function pro_who_ok($s, $type)
{
    $w = $s['who'] ?? 'B';
    return $w === 'B' || ($w === 'I' && $type === 'Individual') || ($w === 'F' && $type !== 'Individual');
}

function pro_services_for($cat, $type)
{
    $ci = pro_categories()[$cat] ?? null;
    if (!$ci) return [];
    return array_values(array_filter(pro_data()['services'], function ($s) use ($ci, $type) {
        return $s['cat'] === $ci['key'] && pro_who_ok($s, $type);
    }));
}

/**
 * Fee row for the chosen type + category. Individuals who pick a service of a higher
 * individual tier (e.g. a licensed T3 service in a T2 category) move up to that tier.
 */
function pro_fee_row($type, $cat, $serviceIds = [])
{
    $ci = pro_categories()[$cat] ?? null;
    if (!$ci || empty($ci['types'][$type])) return null;
    $base = $ci['types'][$type];
    $ord = ['T1' => 1, 'T2' => 2, 'T3' => 3];
    if ($type !== 'Individual' || !isset($ord[$base['tier']])) return $base;
    $top = $base['tier'];
    foreach (pro_data()['services'] as $s) {
        if (in_array($s['id'], $serviceIds, true) && isset($ord[$s['tier']]) && $ord[$s['tier']] > $ord[$top]) $top = $s['tier'];
    }
    if ($top === $base['tier']) return $base;
    $T = pro_data()['tiers'][$top];
    return array_merge($base, ['tier' => $top, 'app' => $T['app'], 'reg' => $T['reg'], 'annual' => $T['annual'],
        'monthly' => $T['monthly'], 'std' => $T['std'], 'launch' => $T['launch'], 'escalated' => true, 'who' => $T['desc']]);
}

/** Firms, partners and store vendors register as a business. */
function pro_is_business($tier) { return in_array($tier, ['T4', 'T5', 'P', 'V'], true); }

/** Documents required for a tier. Each: id, label, req, multi, capture. */
function pro_docs($tier, $cat, $type)
{
    $firm = pro_is_business($tier);
    $inhome = in_array($cat, pro_inhome_cats(), true);
    $d = [];
    $add = function ($id, $label, $req, $extra = []) use (&$d) { $d[] = array_merge(['id' => $id, 'label' => $label, 'req' => $req, 'multi' => false, 'capture' => false], $extra); };

    $add('cnic', $firm ? 'CNIC of authorised signatory (front and back)' : 'CNIC front and back', true, ['multi' => true]);
    if (!$firm) $add('selfie', 'Live selfie (photo of your face)', true, ['capture' => true]);
    if ($tier === 'T1' || ($tier === 'T2' && $inhome)) $add('pcc', 'Police character certificate (issued in the last 180 days)', true);
    elseif (!in_array($tier, ['P', 'V'], true) && $inhome) $add('pcc', 'Police certificate for staff sent to homes', false);
    $add('address', $firm ? 'Office utility bill or rent agreement' : 'Utility bill for your address', true);
    if (in_array($tier, ['T2', 'T3', 'T4', 'T5'], true)) $add('qual', $firm ? 'Key staff qualifications' : 'Qualification or skill certificate', true, ['multi' => true]);
    elseif ($tier === 'T1') $add('qual', 'Skill certificate, if you have one (NAVTTC, TEVTA)', false);
    if (in_array($tier, ['T3', 'T5', 'P'], true)) $add('lic', 'Professional licence or regulator registration', true);
    if (in_array($tier, ['T1', 'T2', 'T3', 'T4'], true)) $add('portfolio', 'Photos of past work or portfolio', false, ['multi' => true]);
    if (in_array($tier, ['T3', 'T4', 'T5', 'P', 'V'], true)) $add('secp', $tier === 'T3' ? 'NTN certificate' : 'SECP certificate and NTN', true);
    if (in_array($tier, ['T4', 'T5'], true)) $add('ins', 'Business insurance policy', true);
    if ($type === 'Vendor - Distributor') $add('auth', 'Principal or brand authorisation letter', true);
    return $d;
}

/** Fee breakdown for the first year. $billing: annual|monthly. */
function pro_fees($fr, $founding, $billing)
{
    $T = pro_data()['tiers'][$fr['tier']];
    if ($fr['tier'] === 'P') return ['partner' => true, 'app' => 0, 'reg' => 0, 'membership' => 0, 'total' => 0];
    $app = $founding ? $fr['app'] * (1 - $T['wApp']) : $fr['app'];
    $reg = $founding ? $fr['reg'] * (1 - $T['wReg']) : $fr['reg'];
    $free = $founding ? (int)$T['free'] : 0;
    $mem = $billing === 'monthly' ? $fr['monthly'] * (12 - $free) : $fr['annual'] * (12 - $free) / 12;
    return [
        'partner' => false, 'founding' => (bool)$founding, 'billing' => $billing, 'free_months' => $free,
        'app_list' => (float)$fr['app'], 'reg_list' => (float)$fr['reg'], 'annual' => (float)$fr['annual'], 'monthly' => (float)$fr['monthly'],
        'app' => round($app), 'reg' => round($reg), 'membership' => round($mem), 'total' => round($app + $reg + $mem),
    ];
}

/** Checks (mandatory M / conditional C) that apply to a tier. */
function pro_checks($tier)
{
    $out = [];
    foreach (pro_data()['checks'] as $c) {
        $v = $c['m'][$tier] ?? '—';
        if ($v === 'M' || $v === 'C') $out[] = ['name' => $c['n'], 'what' => $c['what'], 'conditional' => $v === 'C'];
    }
    return $out;
}

/* ---------- Storage ---------- */

/** Creates the applications table on existing installs (new installs get it from schema.sql). */
function ensure_pro_tables()
{
    static $done = false;
    if ($done) return;
    q("CREATE TABLE IF NOT EXISTS provider_applications (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ref VARCHAR(30) NOT NULL UNIQUE,
        user_id INT UNSIGNED NULL,
        provider_type VARCHAR(40) NOT NULL,
        category VARCHAR(150) NOT NULL,
        tier VARCHAR(4) NOT NULL,
        city VARCHAR(80) NULL,
        waitlist TINYINT(1) NOT NULL DEFAULT 0,
        data LONGTEXT NOT NULL,
        documents LONGTEXT NULL,
        fees LONGTEXT NULL,
        status ENUM('submitted','under_review','approved','rejected') NOT NULL DEFAULT 'submitted',
        admin_notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL,
        KEY user_id (user_id),
        KEY status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done = true;
}

/** Private folder for applicant documents (blocked from the web by .htaccess). */
function pro_doc_dir($ref = '')
{
    $dir = UPLOAD_DIR . '/applications';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    if (!file_exists($dir . '/.htaccess')) @file_put_contents($dir . '/.htaccess', "Require all denied\nOptions -Indexes\n");
    return $ref === '' ? $dir : $dir . '/' . preg_replace('/[^A-Z0-9-]/', '', $ref);
}

function pro_ini_bytes($v)
{
    $v = trim((string)$v);
    $n = (float)$v;
    switch (strtolower(substr($v, -1))) { case 'g': $n *= 1024; case 'm': $n *= 1024; case 'k': $n *= 1024; }
    return (int)$n;
}

/** Upload limits: 5 MB per file, lowered to what php.ini allows. */
function pro_upload_limits()
{
    $file = min(5 * 1024 * 1024, pro_ini_bytes(ini_get('upload_max_filesize')) ?: PHP_INT_MAX);
    $post = pro_ini_bytes(ini_get('post_max_size')) ?: PHP_INT_MAX;
    return ['file' => $file, 'post' => $post, 'count' => (int)ini_get('max_file_uploads') ?: 20];
}

function pro_mb($bytes) { return rtrim(rtrim(number_format($bytes / 1048576, 1), '0'), '.') . ' MB'; }

/**
 * Normalise one $_FILES entry (single or multiple) and check each file.
 * Returns [files[], error|null]. A file: tmp, name, size, ext.
 */
function pro_check_upload($field)
{
    if (empty($_FILES[$field])) return [[], null];
    $f = $_FILES[$field];
    $max = pro_upload_limits()['file'];
    $tooBig = 'Each file must be smaller than ' . pro_mb($max) . '.';
    $list = [];
    foreach ((array)$f['name'] as $i => $name) {
        $err = is_array($f['error']) ? $f['error'][$i] : $f['error'];
        if ($err === UPLOAD_ERR_NO_FILE || $name === '') continue;
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) return [[], $tooBig];
        if ($err !== UPLOAD_ERR_OK) return [[], 'Upload failed. Please try again.'];
        $tmp  = is_array($f['tmp_name']) ? $f['tmp_name'][$i] : $f['tmp_name'];
        $size = is_array($f['size']) ? $f['size'][$i] : $f['size'];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext === 'jpeg') $ext = 'jpg';
        if ($size > $max) return [[], $tooBig];
        if (!in_array($ext, ['jpg', 'png', 'webp', 'pdf'], true)) return [[], 'Upload a JPG, PNG, WEBP image or a PDF.'];
        if ($ext === 'pdf') {
            if (file_get_contents($tmp, false, null, 0, 5) !== '%PDF-') return [[], '"' . $name . '" is not a valid PDF.'];
        } elseif (@getimagesize($tmp) === false) {
            return [[], '"' . $name . '" is not a valid image.'];
        }
        $list[] = ['tmp' => $tmp, 'name' => $name, 'size' => (int)$size, 'ext' => $ext];
    }
    if (count($list) > 10) return [[], 'Upload at most 10 files here.'];
    return [$list, null];
}
