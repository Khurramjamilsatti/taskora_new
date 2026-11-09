<?php
/**
 * Declarative definitions for every public form.
 * field: [name, label, type, required, options/extra]
 * types: text, email, tel, number, date, textarea, select, radio, stars, check, cnic, url
 */
function form_defs()
{
    $cats = array_column(rows('SELECT title FROM categories WHERE is_active = 1 ORDER BY sort_order'), 'title');
    $cities = cities();

    return [
        'booking' => [
            'title' => 'Book a Service', 'icon' => 'calendar', 'prefix' => 'TSK',
            'heading' => 'Service Booking Request',
            'intro' => 'Tell us what you need and where — we will match you with a verified professional nearby.',
            'hint' => 'Typically assigned within 15 minutes during service hours',
            'button' => 'Submit booking request',
            'sections' => [
                'Your details' => [
                    ['name', 'Full name', 'text', true],
                    ['phone', 'Mobile number', 'tel', true, ['placeholder' => '03XX XXXXXXX']],
                    ['email', 'Email address', 'email', false],
                    ['cnic', 'CNIC (optional)', 'cnic', false, ['placeholder' => 'XXXXX-XXXXXXX-X']],
                    ['city', 'City', 'select', true, $cities],
                    ['address', 'Complete address', 'text', true],
                    ['map_link', 'Google Maps location (optional)', 'url', false, ['placeholder' => 'Paste a map link', 'full' => true]],
                ],
                'Service details' => [
                    ['category', 'Main category', 'select', true, $cats],
                    ['service', 'Service required', 'text', true, ['placeholder' => 'e.g. AC Repair & Installation']],
                    ['description', 'Describe the work', 'textarea', true, ['full' => true]],
                    ['urgency', 'Urgency level', 'radio', true, ['normal' => 'Normal', 'urgent' => 'Urgent', 'emergency' => 'Emergency']],
                    ['preferred_date', 'Preferred date', 'date', false],
                ],
                'Pricing & payment' => [
                    ['budget', 'Estimated budget (PKR)', 'number', false],
                    ['quotation_required', 'Quotation required first?', 'radio', true, ['1' => 'Yes', '0' => 'No']],
                    ['payment_method', 'Payment method', 'select', true, ['Cash', 'Bank Transfer', 'Easypaisa', 'JazzCash', 'Card']],
                ],
                'Confirmation' => [
                    ['agree', 'I confirm the information is accurate and accept the Taskora Terms & Conditions.', 'check', true],
                    ['signature', 'Signature (type your full name)', 'text', true, ['full' => true]],
                ],
            ],
        ],
        'company' => [
            'title' => 'Company Registration', 'icon' => 'building', 'prefix' => 'CMP',
            'heading' => 'Corporate Account Registration',
            'intro' => 'Onboard your organisation for corporate contracts, AMC coverage or enterprise solutions.',
            'hint' => 'Corporate accounts are reviewed within 2 business days',
            'button' => 'Submit company profile',
            'sections' => [
                'Company details' => [
                    ['company_name', 'Company name', 'text', true],
                    ['secp_no', 'SECP registration number', 'text', false],
                    ['business_address', 'Business address', 'text', true, ['full' => true]],
                    ['employees', 'Number of employees', 'select', true, ['1–10', '11–50', '51–200', '201–1000', '1000+']],
                    ['industry', 'Industry', 'text', false],
                ],
                'Contact person' => [
                    ['name', 'Contact name', 'text', true],
                    ['designation', 'Designation', 'text', false],
                    ['phone', 'Mobile number', 'tel', true],
                    ['email', 'Email address', 'email', true],
                ],
                'Services & coverage' => [
                    ['services_required', 'Services required', 'textarea', true, ['full' => true, 'placeholder' => 'e.g. janitorial, HVAC AMC, security guards…']],
                    ['coverage', 'Coverage areas / sites', 'text', true, ['full' => true]],
                ],
                'Confirmation' => [
                    ['agree', 'I am authorised to register this company and accept the Taskora Corporate Service Agreement.', 'check', true],
                ],
            ],
        ],
        'feedback' => [
            'title' => 'Feedback', 'icon' => 'star', 'prefix' => 'FBK',
            'heading' => 'Customer Feedback',
            'intro' => 'Rate a completed service — your score feeds directly into the professional\'s performance record.',
            'hint' => 'Every rating is reviewed by our quality team',
            'button' => 'Submit feedback',
            'sections' => [
                'Booking' => [
                    ['booking_ref', 'Booking reference', 'text', true, ['placeholder' => 'TSK-…']],
                    ['name', 'Your name', 'text', true],
                    ['phone', 'Mobile number', 'tel', false],
                    ['provider', 'Professional name', 'text', false],
                ],
                'Your experience' => [
                    ['rating', 'Overall rating', 'stars', true],
                    ['recommend', 'Would you recommend this professional?', 'radio', true, ['yes' => 'Yes', 'no' => 'No']],
                    ['comments', 'Comments', 'textarea', false, ['full' => true]],
                ],
            ],
        ],
        'complaint' => [
            'title' => 'Complaint', 'icon' => 'doc', 'prefix' => 'CMPL',
            'heading' => 'Raise a Complaint',
            'intro' => 'Something did not go to plan? Tell us and our resolution team will investigate.',
            'hint' => 'Target resolution: within 24 hours',
            'button' => 'Submit complaint',
            'sections' => [
                'Booking' => [
                    ['booking_ref', 'Booking number', 'text', true],
                    ['name', 'Your name', 'text', true],
                    ['phone', 'Mobile number', 'tel', true],
                    ['provider', 'Professional / provider', 'text', false],
                ],
                'Complaint' => [
                    ['complaint_type', 'Complaint type', 'select', true, ['Service Quality', 'Late Arrival / No Show', 'Pricing Dispute', 'Property Damage', 'Behaviour', 'Other']],
                    ['resolution', 'Resolution required', 'select', true, ['Re-service', 'Partial Refund', 'Full Refund', 'Apology / Warning']],
                    ['details', 'What happened?', 'textarea', true, ['full' => true]],
                ],
            ],
        ],
        'refund' => [
            'title' => 'Refund Request', 'icon' => 'wallet', 'prefix' => 'RFD',
            'heading' => 'Refund Request',
            'intro' => 'Request a refund for a booking — we review every request and respond within 3 business days.',
            'hint' => 'Refunds go back to the original or preferred method',
            'button' => 'Submit refund request',
            'sections' => [
                'Booking' => [
                    ['booking_ref', 'Booking number', 'text', true],
                    ['name', 'Your name', 'text', true],
                    ['phone', 'Mobile number', 'tel', true],
                    ['email', 'Email address', 'email', false],
                ],
                'Refund' => [
                    ['amount', 'Refund amount (PKR)', 'number', true],
                    ['method', 'Preferred method', 'select', true, ['Original payment method', 'Bank Transfer', 'Easypaisa', 'JazzCash']],
                    ['reason', 'Reason for refund', 'textarea', true, ['full' => true]],
                ],
            ],
        ],
        'insurance' => [
            'title' => 'Insurance Claim', 'icon' => 'shield', 'prefix' => 'INS',
            'heading' => 'Insurance Claim',
            'intro' => 'For incidents covered under a Taskora protection plan or a partner insurance policy.',
            'hint' => 'Claims are processed with our licensed insurance partners',
            'button' => 'Submit insurance claim',
            'sections' => [
                'Policy & booking' => [
                    ['policy_no', 'Policy number', 'text', true],
                    ['booking_ref', 'Booking number', 'text', false],
                    ['name', 'Claimant name', 'text', true],
                    ['phone', 'Mobile number', 'tel', true],
                    ['email', 'Email address', 'email', false],
                ],
                'Incident' => [
                    ['incident_date', 'Incident date', 'date', true],
                    ['claim_amount', 'Claim amount (PKR)', 'number', true],
                    ['incident', 'Describe the incident', 'textarea', true, ['full' => true]],
                ],
            ],
        ],
        'contact' => [
            'title' => 'Contact', 'icon' => 'mail', 'prefix' => 'MSG', 'hidden' => true,
            'heading' => 'Send us a message',
            'intro' => 'Questions, partnerships or press — our team replies within one business day.',
            'hint' => 'We never share your details',
            'button' => 'Send message',
            'sections' => [
                'Message' => [
                    ['name', 'Your name', 'text', true],
                    ['email', 'Email address', 'email', true],
                    ['phone', 'Phone (optional)', 'tel', false],
                    ['subject', 'Subject', 'select', true, ['General enquiry', 'Book a service', 'Corporate / Business', 'Become a professional', 'Market Store partnership', 'Knowledge & guides', 'Media & press']],
                    ['message', 'Message', 'textarea', true, ['full' => true]],
                ],
            ],
        ],
    ];
}

/** Render a single field. */
function render_field($f, $prefill = [])
{
    list($name, $label, $type, $req) = $f;
    $opt = $f[4] ?? [];
    $extra = is_array($opt) && isset($opt['full']) ? $opt : [];
    $full = !empty($extra['full']) || in_array($type, ['textarea', 'check', 'radio', 'stars'], true);
    $ph = $extra['placeholder'] ?? '';
    $v = $prefill[$name] ?? '';
    $id = 'f_' . $name;
    $r = $req ? ' required' : '';
    $star = $req ? ' <span class="req">*</span>' : '';

    echo '<div class="field' . ($full ? ' full' : '') . '">';
    switch ($type) {
        case 'textarea':
            echo '<label for="' . $id . '">' . e($label) . $star . '</label><textarea class="input" id="' . $id . '" name="' . $name . '" placeholder="' . e($ph) . '"' . $r . '>' . e($v) . '</textarea>';
            break;
        case 'select':
            echo '<label for="' . $id . '">' . e($label) . $star . '</label><select class="input" id="' . $id . '" name="' . $name . '"' . $r . '><option value="">Select…</option>';
            foreach ($opt as $o) echo '<option' . ($o === $v ? ' selected' : '') . '>' . e($o) . '</option>';
            echo '</select>';
            break;
        case 'radio':
            echo '<span class="label">' . e($label) . $star . '</span><div class="seg">';
            $i = 0;
            foreach ($opt as $k => $o) { echo '<label><input type="radio" name="' . $name . '" value="' . e($k) . '"' . ($i++ === 0 ? ' checked' : '') . '><span>' . e($o) . '</span></label>'; }
            echo '</div>';
            break;
        case 'stars':
            echo '<span class="label">' . e($label) . $star . '</span><div class="stars-input">';
            for ($s = 5; $s >= 1; $s--) echo '<input type="radio" id="' . $id . $s . '" name="' . $name . '" value="' . $s . '"><label for="' . $id . $s . '" title="' . $s . ' stars">' . icon('star') . '</label>';
            echo '</div>';
            break;
        case 'check':
            echo '<label class="check-row"><input type="checkbox" name="' . $name . '" value="1"' . $r . '><span>' . e($label) . '</span></label>';
            break;
        default:
            $t = $type === 'cnic' ? 'text' : $type;
            $more = $type === 'cnic' ? ' pattern="\d{5}-?\d{7}-?\d"' : ($type === 'number' ? ' min="0" step="1"' : '');
            echo '<label for="' . $id . '">' . e($label) . $star . '</label><input class="input" type="' . $t . '" id="' . $id . '" name="' . $name . '" value="' . e($v) . '" placeholder="' . e($ph) . '"' . $r . $more . '>';
    }
    echo '<div class="field-error"></div></div>';
}

/** Validate POST against a form definition. Returns [clean, errors]. */
function validate_form($def)
{
    $clean = []; $errors = [];
    foreach ($def['sections'] as $fields) {
        foreach ($fields as $f) {
            list($name, $label, $type, $req) = $f;
            $opt = $f[4] ?? [];
            $v = post($name);
            if (is_array($v)) $v = '';
            $v = trim((string)$v);
            if ($req && ($v === '' || ($type === 'check' && $v !== '1'))) { $errors[$name] = $type === 'stars' ? 'Please choose a rating.' : 'This field is required.'; continue; }
            if ($v === '') { $clean[$name] = ''; continue; }
            if (mb_strlen($v) > 5000) { $errors[$name] = 'This is too long.'; continue; }
            switch ($type) {
                case 'email': if (!filter_var($v, FILTER_VALIDATE_EMAIL)) $errors[$name] = 'Enter a valid email address.'; break;
                case 'tel': if (!preg_match('/^[+\d][\d\s-]{6,19}$/', $v)) $errors[$name] = 'Enter a valid phone number.'; break;
                case 'number': if (!is_numeric($v) || $v < 0) $errors[$name] = 'Enter a valid amount.'; break;
                case 'date': if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) $errors[$name] = 'Enter a valid date.'; break;
                case 'url': if (!filter_var($v, FILTER_VALIDATE_URL)) $errors[$name] = 'Enter a valid link.'; break;
                case 'cnic': if (!preg_match('/^\d{5}-?\d{7}-?\d$/', $v)) $errors[$name] = 'Use the format XXXXX-XXXXXXX-X.'; break;
                case 'stars': if (!in_array($v, ['1', '2', '3', '4', '5'], true)) $errors[$name] = 'Please choose a rating.'; break;
                case 'select': if (!in_array($v, $opt, true)) $errors[$name] = 'Please choose an option.'; break;
                case 'radio': if (!array_key_exists($v, $opt)) $errors[$name] = 'Please choose an option.'; break;
            }
            $clean[$name] = $v;
        }
    }
    return [$clean, $errors];
}
