<?php
/**
 * Professional registration wizard (included by account/register.php when role=provider).
 * Steps: 1 Your work · 2 Your details · 3 Documents · 4 Fees and consent.
 * The form posts by AJAX (multipart) to this same URL and gets JSON back.
 */
require_once dirname(__DIR__) . '/includes/pro_registration.php';
ensure_pro_tables();

if (is_post()) pro_submit();

function pro_fail($errors, $step, $message = 'Please fix the highlighted fields.')
{
    json_out(['ok' => false, 'errors' => $errors, 'step' => $step, 'message' => $message]);
}

function pro_submit()
{
    // A request bigger than post_max_size arrives with an empty $_POST.
    if (!$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        pro_fail(['_' => 1], 2, 'Your files are too large to send together (limit ' . pro_mb(pro_upload_limits()['post']) . '). Upload smaller photos or fewer portfolio images.');
    }
    csrf_check();
    if (post('website') !== '') json_out(['ok' => false, 'message' => 'Submission rejected.']);

    $types = pro_types();
    $type = post('ptype');
    $cat  = post('category');
    $svcIds = array_values(array_filter((array)($_POST['services'] ?? []), 'is_string'));
    $E = [[], [], [], []];

    /* ---- Step 1: work ---- */
    if (!isset($types[$type])) { $E[0]['ptype'] = 'Choose how you are registering.'; $type = 'Individual'; }
    $cats = pro_categories();
    if (!isset($cats[$cat]) || empty($cats[$cat]['types'][$type])) $E[0]['category'] = 'Choose a category.';
    $city = post('city');
    if (!in_array($city, pro_cities(), true)) $E[0]['city'] = 'Choose a city.';
    $allowed = [];
    foreach (pro_services_for($cat, $type) as $s) $allowed[$s['id']] = $s;
    $svcIds = array_values(array_intersect($svcIds, array_keys($allowed)));
    if (!$svcIds && !isset($E[0]['category'])) $E[0]['services'] = 'Select at least one service.';
    if ($E[0]) pro_fail($E[0], 0);

    $fr = pro_fee_row($type, $cat, $svcIds);
    $tier = $fr['tier'];
    $biz = pro_is_business($tier);

    /* ---- Step 2: details ---- */
    $reCnic = '/^\d{5}-\d{7}-\d$/';
    $reMob  = '/^03\d{2}-?\d{7}$/';
    if ($biz) {
        foreach (['b_name' => 'Enter the registered business name.', 'b_secp' => 'Enter the SECP or partnership registration number.',
                  'b_ntn' => 'Enter the NTN.', 'b_signatory' => 'Enter the authorised signatory.'] as $k => $m) {
            if (post($k) === '') $E[1][$k] = $m;
        }
        if (!preg_match($reCnic, post('b_scnic'))) $E[1]['b_scnic'] = 'Use the format 12345-1234567-1.';
        $cnic = post('b_scnic');
    } else {
        if (mb_strlen(post('name')) < 3) $E[1]['name'] = 'Enter your full name as on your CNIC.';
        if (post('father') === '') $E[1]['father'] = "Enter your father's or husband's name.";
        if (!preg_match($reCnic, post('cnic'))) $E[1]['cnic'] = 'Use the format 12345-1234567-1.';
        $dob = strtotime(post('dob'));
        if (!$dob) $E[1]['dob'] = 'Enter your date of birth.';
        elseif ($dob > strtotime('-18 years')) $E[1]['dob'] = 'You must be at least 18 years old.';
        if (!ctype_digit(post('experience')) || (int)post('experience') > 60) $E[1]['experience'] = 'Enter years of experience (0–60).';
        $cnic = post('cnic');
    }
    if (empty($E[1]['cnic']) && empty($E[1]['b_scnic'])
        && val("SELECT id FROM users WHERE cnic = ? AND role = 'provider'", [$cnic])) {
        $E[1][$biz ? 'b_scnic' : 'cnic'] = 'An application with this CNIC already exists. Sign in instead.';
    }
    if (!preg_match($reMob, post('mobile'))) $E[1]['mobile'] = 'Use a Pakistani mobile number, e.g. 0300-1234567.';
    $email = strtolower(post('email'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $E[1]['email'] = 'Enter a valid email. You use it to sign in.';
    elseif (val('SELECT id FROM users WHERE email = ?', [$email])) $E[1]['email'] = 'An account with this email already exists.';
    if (post('address') === '') $E[1]['address'] = 'Enter your address.';
    if (post('ref1') === '') $E[1]['ref1'] = 'Add a reference.';
    if (post('ref2') === '') $E[1]['ref2'] = 'Add a second reference.';
    if (strlen(post('password')) < 8) $E[1]['password'] = 'Use at least 8 characters.';
    elseif (post('password') !== post('password2')) $E[1]['password2'] = 'Passwords do not match.';
    if ($E[1]) pro_fail($E[1], 1);

    /* ---- Step 3: documents ---- */
    $docs = pro_docs($tier, $cat, $type);
    $uploads = [];
    foreach ($docs as $d) {
        list($files, $err) = pro_check_upload('doc_' . $d['id']);
        if ($err) $E[2]['doc_' . $d['id']] = $err;
        elseif ($d['req'] && !$files) $E[2]['doc_' . $d['id']] = 'Upload this document.';
        elseif ($files) $uploads[$d['id']] = ['label' => $d['label'], 'files' => $files];
    }
    if ($E[2]) pro_fail($E[2], 2);

    /* ---- Step 4: fees and consent ---- */
    $billing = post('billing') === 'monthly' ? 'monthly' : 'annual';
    $founding = post('founding') === '1';
    $payMethods = ['JazzCash', 'Easypaisa', 'Raast', 'Bank transfer (IBFT)', 'Debit / credit card'];
    $payMethod = in_array(post('pay_method'), $payMethods, true) ? post('pay_method') : $payMethods[0];
    $payout = preg_replace('/\s+/', '', strtoupper(post('payout')));
    if ($tier !== 'P' && !preg_match('/^PK\d{2}[A-Z]{4}[A-Z0-9]{16}$/', $payout) && !preg_match($reMob, $payout)) {
        $E[3]['payout'] = 'Enter a 24-character IBAN (PK…) or a mobile wallet number (03XX-XXXXXXX).';
    }
    if (count(array_filter((array)($_POST['decl'] ?? []))) < 4) $E[3]['decl'] = 'Tick all four declarations to submit.';
    if ($E[3]) pro_fail($E[3], 3);

    if (!throttle('register', 5, 600)) pro_fail(['_' => 1], 3, 'Too many applications from this session. Please try again later.');

    /* ---- Save ---- */
    $fees = pro_fees($fr, $founding, $billing);
    $services = array_map(function ($id) use ($allowed) { return ['id' => $id, 'name' => $allowed[$id]['name'], 'tier' => $allowed[$id]['tier']]; }, $svcIds);
    $applicant = $biz
        ? ['business_name' => post('b_name'), 'secp' => post('b_secp'), 'ntn' => post('b_ntn'), 'strn' => post('b_strn'), 'staff' => post('b_staff'),
           'licence' => post('b_lic'), 'signatory' => post('b_signatory'), 'signatory_cnic' => post('b_scnic')]
        : ['name' => post('name'), 'father_name' => post('father'), 'cnic' => post('cnic'), 'dob' => post('dob'),
           'experience_years' => (int)post('experience'), 'qualification' => post('qual')];
    $displayName = $biz ? post('b_name') : post('name');
    $ref = make_ref('APP');
    $data = [
        'services' => $services, 'areas' => post('areas'), 'applicant' => $applicant,
        'contact' => ['mobile' => post('mobile'), 'email' => $email, 'address' => post('address'), 'references' => [post('ref1'), post('ref2')]],
        'payout_account' => $payout, 'preferred_payment' => $payMethod, 'declarations_accepted' => true,
        'fee_row' => ['who' => $fr['who'] ?? '', 'licence' => $fr['lic'] ?? '', 'escalated' => !empty($fr['escalated'])],
        'ip' => client_ip(), 'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ];
    $subcat = implode(', ', array_column($services, 'name'));
    if (mb_strlen($subcat) > 150) $subcat = mb_substr($subcat, 0, 147) . '…';

    $dir = pro_doc_dir($ref);
    $stored = [];
    $pdo = db();
    $pdo->beginTransaction();
    try {
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) throw new RuntimeException('Could not create the document folder.');
        foreach ($uploads as $id => $u) {
            foreach ($u['files'] as $i => $f) {
                $file = $id . '-' . ($i + 1) . '-' . bin2hex(random_bytes(4)) . '.' . $f['ext'];
                if (!move_uploaded_file($f['tmp'], $dir . '/' . $file)) throw new RuntimeException('Could not save "' . $f['name'] . '".');
                $stored[$id]['label'] = $u['label'];
                $stored[$id]['files'][] = ['file' => $file, 'name' => mb_substr($f['name'], 0, 120), 'size' => $f['size']];
            }
        }
        q('INSERT INTO users (role, name, email, phone, cnic, city, password, category, subcategory, experience, service_areas, qualifications, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            'provider', $displayName, $email, post('mobile'), $cnic, $city, password_hash(post('password'), PASSWORD_DEFAULT),
            $cat, $subcat, $biz ? null : (int)post('experience'), mb_substr(post('areas') ?: $city, 0, 255),
            $biz ? trim(post('b_lic')) : post('qual'), 'pending',
        ]);
        $uid = (int)$pdo->lastInsertId();
        q('INSERT INTO provider_applications (ref, user_id, provider_type, category, tier, city, waitlist, data, documents, fees) VALUES (?,?,?,?,?,?,?,?,?,?)', [
            $ref, $uid, $type, $cat, $tier, $city, in_array($city, pro_launch_cities(), true) ? 0 : 1,
            json_encode($data, JSON_UNESCAPED_UNICODE), json_encode($stored, JSON_UNESCAPED_UNICODE), json_encode($fees),
        ]);
        $pdo->commit();
    } catch (Exception $ex) {
        $pdo->rollBack();
        foreach (glob($dir . '/*') ?: [] as $f) @unlink($f);
        @rmdir($dir);
        json_out(['ok' => false, 'step' => 3, 'errors' => ['_' => 1],
            'message' => 'We could not save your application. Please try again.' . (APP_DEBUG ? ' (' . $ex->getMessage() . ')' : '')]);
    }

    session_regenerate_id(true);
    $_SESSION['uid'] = $uid;
    notify($uid, 'Application ' . $ref . ' received',
        'We are checking your CNIC and mobile number, usually within 24 hours. Then we send a payment link for the application fee by SMS and WhatsApp.', 'account/index.php');
    flash('success', 'Application ' . $ref . ' submitted. Our team will verify your profile shortly.');
    json_out(['ok' => true, 'ref' => $ref, 'redirect' => base_url('account/index.php')]);
}

/* ---------- Page ---------- */
$d = pro_data();
$catsOut = [];
foreach (pro_categories() as $name => $ci) {
    $types = [];
    foreach ($ci['types'] as $t => $f) {
        $types[$t] = ['tier' => $f['tier'], 'app' => $f['app'], 'reg' => $f['reg'], 'annual' => $f['annual'], 'monthly' => $f['monthly'], 'who' => $f['who'] ?? '', 'lic' => $f['lic'] ?? ''];
    }
    $catsOut[$name] = ['types' => $types, 'req' => $d['catMeta'][$ci['key']]['req'] ?? []];
}
$client = [
    'cats' => $catsOut,
    'services' => array_map(function ($s) { return [$s['id'], pro_clean_cat($s['cat']), $s['sub'], $s['name'], $s['tier'], $s['phase'], $s['who']]; }, $d['services']),
    'tiers' => $d['tiers'],
    'checks' => array_map(function ($c) { return ['n' => $c['n'], 'what' => $c['what'], 'm' => $c['m']]; }, $d['checks']),
    'launch' => pro_launch_cities(),
    'inhome' => pro_inhome_cats(),
    'limits' => pro_upload_limits(),
];
$hire = ['Registration', 'Document verification', 'Background check', 'Skill assessment', 'Training and onboarding', 'Profile activation', 'Performance review'];

render_head('Join as a professional | ' . setting('site_name'), 'Register as a verified Taskora professional: choose your services, upload your documents and see your fees.', ['body' => 'has-hero', 'css' => ['css/pro-register.css']]);
render_header();
?>
<main>
<section class="page-hero preg-hero grain" style="padding-bottom:90px">
  <div class="orb orb-green"></div><div class="orb orb-gold"></div>
  <div class="container">
    <div class="crumbs" data-reveal><a href="<?= e(base_url('index.php')) ?>">Home</a><?= icon('chev') ?><span>Join as a professional</span></div>
    <span class="eyebrow eyebrow-gold" data-reveal>Pro registration</span>
    <h1 class="display split-words">Every professional, <em>checked before they earn.</em></h1>
    <p class="preg-ur" lang="ur" dir="rtl" data-reveal>ہر پیشہ ور، تصدیق کے بعد</p>
    <p class="lede" data-reveal data-delay="3">Takes about 10 minutes. We ask for fees only after your CNIC and mobile number are confirmed, and never in cash or to a personal account.</p>
  </div>
</section>

<section class="section preg-section">
  <div class="container preg">
    <div class="preg-card" id="pregWrap">
      <details class="preg-hire">
        <summary><?= icon('shield') ?><b>How we hire: read before you apply</b><?= icon('chev-down', 'preg-chev') ?></summary>
        <ol class="preg-flow"><?php foreach ($hire as $h): ?><li><?= e($h) ?></li><?php endforeach; ?></ol>
        <div class="preg-grid2">
          <div><b>Every provider needs</b><ul class="preg-plain"><li>Valid CNIC</li><li>Professional or educational certificates (if applicable)</li><li>Experience proof and portfolio (if applicable)</li><li>Police certificate for security-sensitive and in-home work</li><li>Active bank account, Easypaisa or JazzCash</li><li>Professional profile picture</li><li>Agreement to Taskora terms</li></ul></div>
          <div><b>Terms in short</b><ul class="preg-plain"><li>Give true information. False details mean disqualification.</li><li>Follow the Code of Conduct and quality, safety and timing standards</li><li>Keep customer data private</li><li>Payments follow Taskora's payout cycle</li><li>You are responsible for your own taxes</li><li>Violations lead to a warning, suspension, removal or legal action</li></ul>
            <p class="preg-hint">Taskora is an equal-opportunity platform: no discrimination by gender, religion, caste, colour or disability.</p></div>
        </div>
      </details>

      <ol class="preg-steps" id="pregSteps"><li>Your work</li><li>Your details</li><li>Documents</li><li>Fees and consent</li></ol>
      <p class="preg-step-label" id="pregStepLabel" aria-live="polite"></p>
      <div class="preg-alert" id="pregAlert" role="alert" hidden></div>

      <form id="pregForm" action="<?= e(base_url('account/register.php?role=provider')) ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

        <!-- Step 1 -->
        <div class="preg-step" data-step="0">
          <fieldset class="field"><legend class="label">You are registering as <span class="req">*</span></legend>
            <div class="preg-seg" id="ptype">
              <?php $i = 0; foreach (pro_types() as $v => $l): ?>
                <input type="radio" name="ptype" id="pt<?= $i ?>" value="<?= e($v) ?>"<?= $i === 0 ? ' checked' : '' ?>><label for="pt<?= $i ?>"><?= e($l) ?></label>
              <?php $i++; endforeach; ?>
            </div>
          </fieldset>
          <div class="preg-grid2">
            <div class="field"><label for="category">Service category <span class="req">*</span></label><select class="input" id="category" name="category"></select><p class="preg-hint" id="catHint"></p></div>
            <div class="field"><label for="city">City you will work in <span class="req">*</span></label>
              <select class="input" id="city" name="city"><option value="">Select city</option><?php foreach (pro_cities() as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select>
              <p class="preg-hint" id="cityHint"></p></div>
          </div>
          <div class="field"><label for="areas">Areas or sectors you cover</label><input class="input" id="areas" name="areas" placeholder="e.g. G-9, G-10, F-10, Bahria Town"></div>
          <fieldset class="field" data-err="services"><legend class="label">Services you offer <span class="req">*</span> <span class="preg-count" id="svcCount"></span></legend>
            <div id="svcBox"></div>
          </fieldset>
        </div>

        <!-- Step 2 -->
        <div class="preg-step" data-step="1" hidden>
          <div id="indivFields">
            <h3 class="preg-h3">About you</h3>
            <div class="preg-grid2">
              <div class="field"><label for="name">Full name as on CNIC <span class="req">*</span></label><input class="input" id="name" name="name" autocomplete="name"></div>
              <div class="field"><label for="father">Father's or husband's name <span class="req">*</span></label><input class="input" id="father" name="father"></div>
              <div class="field"><label for="cnic">CNIC number <span class="req">*</span></label><input class="input" id="cnic" name="cnic" inputmode="numeric" placeholder="61101-1234567-1" maxlength="15" data-cnic><p class="preg-hint">13 digits; dashes are added for you</p></div>
              <div class="field"><label for="dob">Date of birth <span class="req">*</span></label><input class="input" type="date" id="dob" name="dob" max="<?= e(date('Y-m-d', strtotime('-18 years'))) ?>"></div>
              <div class="field"><label for="experience">Years of experience <span class="req">*</span></label><input class="input" type="number" id="experience" name="experience" min="0" max="60"></div>
              <div class="field"><label for="qual">Highest qualification or skill certificate</label><input class="input" id="qual" name="qual" placeholder="e.g. TEVTA Diploma – Electrical"></div>
            </div>
          </div>
          <div id="bizFields" hidden>
            <h3 class="preg-h3">About your business</h3>
            <div class="preg-grid2">
              <div class="field"><label for="b_name">Registered business name <span class="req">*</span></label><input class="input" id="b_name" name="b_name" autocomplete="organization"></div>
              <div class="field"><label for="b_secp">SECP registration or partnership deed no. <span class="req">*</span></label><input class="input" id="b_secp" name="b_secp"></div>
              <div class="field"><label for="b_ntn">NTN <span class="req">*</span></label><input class="input" id="b_ntn" name="b_ntn" placeholder="1234567-8"></div>
              <div class="field"><label for="b_strn">STRN (if registered for sales tax)</label><input class="input" id="b_strn" name="b_strn"></div>
              <div class="field"><label for="b_staff">Number of staff or technicians</label><input class="input" type="number" id="b_staff" name="b_staff" min="1"></div>
              <div class="field"><label for="b_lic">Licence or regulator registration no.</label><input class="input" id="b_lic" name="b_lic"></div>
              <div class="field"><label for="b_signatory">Authorised signatory name <span class="req">*</span></label><input class="input" id="b_signatory" name="b_signatory"></div>
              <div class="field"><label for="b_scnic">Signatory CNIC <span class="req">*</span></label><input class="input" id="b_scnic" name="b_scnic" inputmode="numeric" placeholder="61101-1234567-1" maxlength="15" data-cnic></div>
            </div>
          </div>
          <h3 class="preg-h3">Contact</h3>
          <div class="preg-grid2">
            <div class="field"><label for="mobile">Mobile number (in your name) <span class="req">*</span></label><input class="input" type="tel" id="mobile" name="mobile" inputmode="tel" placeholder="0300-1234567" autocomplete="tel"></div>
            <div class="field"><label for="email">Email <span class="req">*</span></label><input class="input" type="email" id="email" name="email" autocomplete="email"><p class="preg-hint">You sign in with this email.</p></div>
            <div class="field preg-full"><label for="address">Address <span class="req">*</span></label><textarea class="input" id="address" name="address" rows="2" autocomplete="street-address"></textarea></div>
            <div class="field"><label for="ref1">Reference 1 (name and phone) <span class="req">*</span></label><input class="input" id="ref1" name="ref1"></div>
            <div class="field"><label for="ref2">Reference 2 (name and phone) <span class="req">*</span></label><input class="input" id="ref2" name="ref2"></div>
          </div>
          <h3 class="preg-h3">Your Taskora login</h3>
          <div class="preg-grid2">
            <div class="field"><label for="password">Password <span class="req">*</span></label><input class="input" type="password" id="password" name="password" autocomplete="new-password" minlength="8"><p class="preg-hint">At least 8 characters.</p></div>
            <div class="field"><label for="password2">Confirm password <span class="req">*</span></label><input class="input" type="password" id="password2" name="password2" autocomplete="new-password"></div>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="preg-step" data-step="2" hidden>
          <h3 class="preg-h3">Documents for your tier</h3>
          <p class="preg-hint" id="tierLine" style="margin-top:0"></p>
          <p class="preg-hint">JPG, PNG, WEBP or PDF, up to <?= e(pro_mb($client['limits']['file'])) ?> each. Photos are resized on your phone before upload.</p>
          <ul class="preg-docs" id="docList"></ul>
          <h3 class="preg-h3">Checks we run before your profile goes live</h3>
          <div class="preg-checks" id="checkList"></div>
          <p class="preg-hint">Checks marked "if applicable" apply only when the rule fits your work, for example police clearance for work inside homes.</p>
        </div>

        <!-- Step 4 -->
        <div class="preg-step" data-step="3" hidden>
          <div class="preg-grid2 preg-top">
            <div>
              <h3 class="preg-h3">Your fees</h3>
              <div class="preg-receipt" id="receipt"></div>
              <div class="preg-notice" id="feeNote"></div>
            </div>
            <div>
              <div id="feeOptions">
                <fieldset class="field"><legend class="label">Membership billing</legend>
                  <div class="preg-seg" id="billing">
                    <input type="radio" name="billing" id="billA" value="annual" checked><label for="billA">Yearly</label>
                    <input type="radio" name="billing" id="billM" value="monthly"><label for="billM">Monthly</label>
                  </div>
                </fieldset>
                <label class="preg-chk"><input type="checkbox" id="founding" name="founding" value="1" checked> <span>Apply for the founding-partner launch offer</span></label>
                <div class="field" style="margin-top:16px"><label for="pay_method">Preferred payment method (after pre-screen approval)</label>
                  <select class="input" id="pay_method" name="pay_method"><option>JazzCash</option><option>Easypaisa</option><option>Raast</option><option>Bank transfer (IBFT)</option><option>Debit / credit card</option></select></div>
                <div class="field"><label for="payout">Payout account (bank IBAN or mobile wallet) <span class="req">*</span></label><input class="input" id="payout" name="payout" placeholder="PK36SCBL… or 0300-1234567"><p class="preg-hint">A 24-character IBAN starting with PK, or a JazzCash / Easypaisa number.</p></div>
              </div>
              <fieldset class="field" data-err="decl"><legend class="label">Declarations <span class="req">*</span></legend>
                <label class="preg-chk"><input type="checkbox" name="decl[]" value="1"> <span>All information and documents I provide are true. False information leads to rejection and blacklisting.</span></label>
                <label class="preg-chk"><input type="checkbox" name="decl[]" value="2"> <span>I agree to Taskora checking my identity, records, references and background through NADRA, police, regulators and authorised vendors.</span></label>
                <label class="preg-chk"><input type="checkbox" name="decl[]" value="3"> <span>I understand the application fee is non-refundable, and the registration fee is refunded if I am rejected for reasons other than fraud.</span></label>
                <label class="preg-chk"><input type="checkbox" name="decl[]" value="4"> <span>I agree to the <a href="<?= e(base_url('page.php?slug=terms')) ?>" target="_blank">Partner Terms</a>, Code of Conduct and commission schedule. I will take Taskora jobs only through the app.</span></label>
              </fieldset>
            </div>
          </div>
        </div>

        <div class="preg-actions">
          <button class="btn btn-ghost on-light" id="pregBack" type="button" hidden><span>Back</span></button>
          <span class="preg-actions-alt">Already registered? <a href="<?= e(base_url('account/login.php?role=provider')) ?>">Sign in</a></span>
          <button class="btn btn-gold" id="pregNext" type="button"><span>Continue</span><?= icon('arrow') ?></button>
        </div>
      </form>
    </div>
    <div class="preg-card preg-done" id="pregDone" hidden tabindex="-1"></div>
    <p class="preg-foot">Fees shown are the current proposed schedule and may change before launch. Looking for a customer account? <a href="<?= e(base_url('account/register.php')) ?>">Create one here</a>.</p>
  </div>
</section>
</main>
<script type="application/json" id="pregData"><?= json_encode($client, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php render_footer(['js' => ['js/pro-register.js']]); ?>
