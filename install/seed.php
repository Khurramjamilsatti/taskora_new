<?php
/**
 * Seed content for a fresh Taskora install.
 * Returns an array consumed by install.php. All copy is editable later in the CMS.
 */

$sections = [];
$add = function ($key, $page, $label, $tag, $title, $emph, $desc, $data) use (&$sections) {
    $sections[] = compact('key', 'page', 'label', 'tag', 'title', 'emph', 'desc', 'data');
};

/* ================= HOME ================= */
$add('promo', 'home', 'Promo Strip', '', 'Now launching across Pakistan — your first booking is up to 20% off', '', '', [
    'link_label' => 'Claim offer', 'link_href' => '#download',
]);

$add('hero', 'home', 'Hero', "Pakistan's verified services ecosystem", "One app.\nEvery service,\nevery need.", 'every need.',
    'From a midnight electrician to an enterprise facility contract — discover, compare and book 10,000+ vetted professionals across 100+ services, then follow every job live until it is done.',
    [
        'ctas' => [
            ['label' => 'Download the App', 'href' => '#download', 'variant' => 'gold'],
            ['label' => 'Explore Services', 'href' => 'catalogue.php', 'variant' => 'ghost'],
        ],
        'stats' => [
            ['value' => '16+', 'label' => 'Categories'],
            ['value' => '100+', 'label' => 'Core Services'],
            ['value' => '10,000+', 'label' => 'Verified Pros'],
            ['value' => '24/7', 'label' => 'Support'],
        ],
        'phone_categories' => ['Home', 'Business', 'Facility', 'Construction'],
        'popular_services' => [
            ['name' => 'Deep Cleaning', 'rating' => '4.9', 'price' => 'from Rs 900'],
            ['name' => 'Electrician Visit', 'rating' => '4.8', 'price' => 'from Rs 1,200'],
            ['name' => 'AC Service', 'rating' => '4.7', 'price' => 'from Rs 2,200'],
        ],
        'ticker' => ['Home Services', 'Construction', 'Architecture', 'Legal', 'Chartered Accountants', 'IT & Software', 'Tutors & Education', 'Corporate Services', 'Facility Management', 'HR & Recruitment', 'Digital Marketing', 'Health & Wellness', 'Event Management', 'Logistics & Transport', 'Security Services', 'Insurance & Finance'],
    ]);

$add('stats_bar', 'home', 'Stats Bar', '', '', '', '', [
    'items' => [
        ['value' => '100+', 'label' => 'Service Categories'],
        ['value' => '10,000+', 'label' => 'Verified Professionals'],
        ['value' => '50,000+', 'label' => 'Happy Customers'],
        ['value' => '100%', 'label' => 'Satisfaction Guarantee'],
    ],
]);

$add('company', 'home', 'Who We Are', 'Who we are', "Crafted for the way\nPakistan gets things done.", 'gets things done.',
    'Taskora brings home care, business support, facility operations, architecture and construction under one roof — a single, trusted place to find the right expert and see the work through.',
    [
        'why_items' => [
            'Every professional is screened before they ever reach your door.',
            'Clear, upfront pricing — what you see is what you pay.',
            'Live status from the moment you book until the job is signed off.',
            'Protected digital payments on every single transaction.',
            'Scales from one apartment to multi-site enterprise contracts.',
        ],
        'badge_value' => '16', 'badge_label' => 'service categories under one roof',
    ]);

$add('flow', 'home', 'Customer Journey', 'What sets Taskora apart', "Beyond the booking.\nThe entire journey.", 'The entire journey.',
    'Most apps hand you a contact and walk away. Taskora stays with you — from your very first question to the finished job, and long after it.',
    [
        'steps' => [
            ['title' => 'Learn', 'description' => 'Guides and checklists'],
            ['title' => 'Compare', 'description' => 'Profiles side by side'],
            ['title' => 'Estimate', 'description' => 'Instant cost ranges'],
            ['title' => 'Plan', 'description' => 'Dates, scope, budget'],
            ['title' => 'Book', 'description' => 'Confirmed in minutes'],
            ['title' => 'Track', 'description' => 'Live job status'],
            ['title' => 'Pay', 'description' => 'Secure & digital'],
            ['title' => 'Review', 'description' => 'Rate every job'],
            ['title' => 'Maintain', 'description' => 'Plans & reminders'],
        ],
    ]);

$add('services', 'home', 'Services Directory (heading)', 'Service directory — 16 categories, 100+ core services', "Everything you need,\nbeautifully organised.", 'beautifully organised.',
    'A curated directory spanning home, business, professional and property services — every listing held to one verification standard.',
    [
        'disclaimer' => 'Taskora never holds customer investments. Financial products are offered only through licensed banks, insurers and brokerage partners.',
    ]);

$add('market_store', 'home', 'Market Store', 'New — materials marketplace', "Taskora\nMarket Store.", 'Market Store.',
    'Verified materials at wholesale rates for technicians, and itemised, honest invoices for customers — one store across every trade.',
    [
        'pricing_tag' => 'Wholesale price engine',
        'pricing_title' => 'Honest numbers, every order.',
        'pricing_note' => 'Sample figures for illustration — live prices refresh continuously from verified distributors.',
        'headers' => ['Product', 'Market Price', 'Wholesale', 'Taskora Partner', 'You Save'],
        'rows' => [
            ['product' => 'PVC Pipe', 'market' => 'Rs 480', 'wholesale' => 'Rs 430', 'partner' => 'Rs 415', 'save' => 'Rs 65'],
            ['product' => 'Paint (20L)', 'market' => 'Rs 11,000', 'wholesale' => 'Rs 10,300', 'partner' => 'Rs 9,950', 'save' => 'Rs 1,050'],
            ['product' => 'LED Bulb', 'market' => 'Rs 900', 'wholesale' => 'Rs 760', 'partner' => 'Rs 720', 'save' => 'Rs 180'],
            ['product' => 'Cement Bag', 'market' => 'Rs 1,600', 'wholesale' => 'Rs 1,500', 'partner' => 'Rs 1,450', 'save' => 'Rs 150'],
        ],
        'audiences' => [
            ['tag' => 'For Technicians', 'title' => 'Source like a pro', 'description' => 'Compare live prices and have materials sent straight to site.', 'items' => 'Verified product catalogue, One-tap quotation builder, Purchase history & invoices, Healthier cash flow'],
            ['tag' => 'For Customers', 'title' => 'Zero surprise mark-ups', 'description' => 'Know exactly what each material costs before work begins.', 'items' => 'Transparent material pricing, Genuine branded products, Digital warranties, Itemised professional invoices'],
            ['tag' => 'For Suppliers', 'title' => 'Sell to more buyers', 'description' => 'Reach verified technicians and businesses across the country.', 'items' => 'Bulk order access, Digital inventory tools, Nationwide reach, Demand & trend analytics'],
        ],
        'categories' => ['Plumbing', 'Electrical', 'Construction', 'Hardware & Tools', 'Paints & Coatings', 'AC & Cooling', 'Cleaning Supplies', 'IT & Electronics', 'Furniture & Decor', 'Automotive', 'Laundry Supplies', 'Packaging & Moving', 'Safety & Security', 'Solar & Energy', 'Agriculture', 'Office Supplies', 'HR & Uniforms', 'Medical & First Aid', 'Legal & Stationery', 'Marketing & Branding'],
        'how_tag' => 'How it works',
        'how_title' => 'From search to same-day delivery.',
        'how_steps' => [
            ['title' => 'Search', 'description' => 'Find exactly the item you need.'],
            ['title' => 'Compare', 'description' => 'Market vs. wholesale, side by side.'],
            ['title' => 'Order', 'description' => 'Check out securely in seconds.'],
            ['title' => 'Delivered', 'description' => 'Dispatched quickly to the site.'],
            ['title' => 'Use & Grow', 'description' => 'Better margins, repeat clients.'],
        ],
    ]);

$add('knowledge', 'home', 'Knowledge Base', 'Beyond the marketplace', "Pakistan's richest\nhome & property\nknowledge library.", 'knowledge library.',
    'Practical guides, checklists and estimators that help you decide with confidence — long before you book anyone.',
    [
        'guides' => ['Home maintenance guides', 'Construction planning resources', 'Architecture & interior inspiration', 'Renovation checklists', 'Cost estimation articles', 'Facility management best practices', 'Energy-saving & smart home tips', 'Seasonal maintenance calendars'],
    ]);

$add('calculator', 'home', 'Cost Estimator', 'Smart digital tools', "Try the cost\nestimator.", 'estimator.',
    'See an instant, indicative price range before you book. Your professional always confirms the final figure first.',
    [
        'note' => 'Indicative estimate based on typical bookings. The assigned professional confirms your final price before any work starts — no hidden charges.',
        'size_min' => 3, 'size_max' => 20, 'size_default' => 5, 'size_unit' => 'Marla',
        'service_types' => [
            ['id' => 'house_cleaning', 'label' => 'House Cleaning', 'base_price' => 450],
            ['id' => 'deep_cleaning', 'label' => 'Deep Cleaning', 'base_price' => 900],
            ['id' => 'electrician', 'label' => 'Electrician Visit & Repair', 'base_price' => 1200],
            ['id' => 'plumbing', 'label' => 'Plumbing Repair', 'base_price' => 1500],
            ['id' => 'ac_service', 'label' => 'AC Service & Repair', 'base_price' => 2200],
            ['id' => 'painting', 'label' => 'Painting (per room)', 'base_price' => 3500],
            ['id' => 'movers', 'label' => 'Packers & Movers', 'base_price' => 6000],
        ],
        'frequencies' => [
            ['id' => 'one_time', 'label' => 'One-Time', 'factor' => 1],
            ['id' => 'weekly', 'label' => 'Weekly · 10% off', 'factor' => 0.9],
            ['id' => 'monthly', 'label' => 'Monthly · 15% off', 'factor' => 0.85],
        ],
    ]);

$add('tools', 'home', 'Planning Tools', 'More planning tools', 'Plan first. Book with confidence.', 'Book with confidence.', '', [
    'items' => ['Construction budget calculator', 'Paint & tile quantity calculator', 'Solar savings calculator', 'AMC savings calculator', 'Home maintenance planner', 'Property inspection checklist', 'Project timeline planner', 'Moving cost estimator'],
]);

$add('dashboard', 'home', 'Customer Dashboard Preview', 'Your account', "A private dashboard\nfor every customer.", 'for every customer.',
    'Follow bookings, settle payments, keep your favourite professionals close and rate every job — all in one elegant account.',
    [
        'nav' => ['Bookings', 'Payments & Invoices', 'Saved Professionals', 'My Reviews', 'Account Settings'],
        'user_name' => 'Ayesha Khan', 'user_email' => 'ayesha@example.com',
        'cards' => [
            ['label' => 'Total Bookings', 'value' => '12'],
            ['label' => 'Total Spent', 'value' => 'PKR 38,400'],
            ['label' => 'Avg. Rating Given', 'value' => '4.9★'],
        ],
        'bookings' => [
            ['who' => 'Bilal Ahmed — Electrician', 'what' => 'Circuit repair · Today, 4:30 PM · DHA Phase 5', 'status' => 'En Route'],
            ['who' => 'CleanPro Team — Deep Cleaning', 'what' => '3-bed apartment · Tomorrow, 10:00 AM', 'status' => 'Confirmed'],
        ],
        'rate_title' => 'How was your last service?',
        'rate_subtitle' => 'AC Service with Imran M. — completed Tuesday',
    ]);

$add('reviews', 'home', 'Ratings & Reviews', 'Ratings & reviews', "Rated highly by\nreal bookings.", 'real bookings.', '', [
    'score' => '4.8', 'count' => 'Across 28,600+ reviews',
    'note' => 'Sample reviews shown for demonstration.',
    'distribution' => [
        ['stars' => 5, 'percent' => 78], ['stars' => 4, 'percent' => 15], ['stars' => 3, 'percent' => 4],
        ['stars' => 2, 'percent' => 2], ['stars' => 1, 'percent' => 1],
    ],
]);

$add('career', 'home', 'Professional Career Path', 'Professional network', "From first sign-up\nto regional leader.", 'to regional leader.',
    'Every professional passes through one verified pipeline — then rises through a career ladder that rewards great work, openly and fairly.',
    [
        'steps' => [
            ['title' => 'Registration', 'description' => 'Sign up digitally in minutes.'],
            ['title' => 'Verification', 'description' => 'Identity and documents reviewed.'],
            ['title' => 'Training', 'description' => 'Skills assessed, onboarding done.'],
            ['title' => 'Activation', 'description' => 'Your profile goes live.'],
            ['title' => 'Job Assignment', 'description' => 'Matched with nearby bookings.'],
            ['title' => 'Performance', 'description' => 'Rated after every job.'],
            ['title' => 'Growth', 'description' => 'Unlock the next level.'],
        ],
        'levels' => ['Bronze', 'Silver', 'Gold', 'Platinum', 'Master Pro', 'Team Leader', 'Regional Manager'],
    ]);

$add('solutions', 'home', 'Business Solutions', 'Beyond the household', "Solutions sized\nfor every scale.", 'for every scale.',
    'One partner, one dashboard and complete operational support — whether you run a home, a housing society or a portfolio of sites.',
    [
        'items' => [
            ['tag' => 'For Business', 'title' => 'Business Solutions', 'description' => 'Run facility operations, maintenance requests, vendors and recurring services from one central platform.', 'features' => 'Corporate office facility management, Recurring maintenance contracts, Vendor consolidation, Digital reporting dashboard'],
            ['tag' => 'For Communities', 'title' => 'Housing Society Solutions', 'description' => 'Purpose-built digital tools for modern residential communities and the committees that run them.', 'features' => 'Resident service portal, Complaint & visitor management, Community announcements, Society-wide dashboard'],
            ['tag' => 'For Enterprise', 'title' => 'Enterprise Solutions', 'description' => 'Multi-site coverage for hospitality, healthcare, education and industrial facilities.', 'features' => 'Multi-location coordination, Compliance-ready reporting, Dedicated account management, Custom SLAs'],
        ],
    ]);

$add('marketplace', 'home', 'Community Marketplace', 'Community marketplace', "Where customers and\nprofessionals meet.", 'professionals meet.',
    'A living showcase of real work — reviews, portfolios and before-and-after galleries that let quality speak for itself.',
    [
        'tags' => ['Verified Reviews', 'Professional Portfolios', 'Before & After Galleries', 'Q&A Forums', 'Expert Recommendations', 'Success Stories', 'Seasonal Campaigns', 'Local Recommendations'],
    ]);

$add('trust', 'home', 'Trust & Safety', 'Trust & safety', 'Verified before every dispatch.', 'every dispatch.',
    'Onboarding can include identity checks, document review, skills assessment and ongoing quality monitoring. The exact steps depend on the service category and applicable law.',
    [
        'checks' => ['Identity Verification', 'Document Review', 'Skills Assessment', 'Customer Quality Monitoring', 'Secure In-App Payments'],
        'badges' => ['SECP Registered', 'Data Privacy Compliant', 'Secure Cloud Hosting', 'Audit Logged'],
    ]);

$add('customers', 'home', 'Who We Serve', 'Who we serve', "Made for every kind\nof customer.", 'of customer.',
    'From a single flat to a network of industrial sites — the platform grows right alongside you.',
    [
        'tags' => ['Homeowners', 'Apartment Residents', 'Overseas Pakistanis', 'Property Investors', 'Restaurants & Hotels', 'Hospitals & Schools', 'Banks', 'Developers', 'Housing Societies', 'Government Organizations', 'Industrial Facilities'],
    ]);

$add('business_model', 'home', 'Business Model', 'Business model', "How Taskora keeps\nthe platform strong.", 'the platform strong.',
    'Diversified, transparent revenue — designed to keep the service dependable for customers and fair for professionals.',
    [
        'notes' => ['Indicative planning split based on growth assumptions — not a financial guarantee.', 'The Market Store adds further upside beyond this split: material margin, wholesale subscriptions and delivery fees.'],
        'streams' => [
            ['label' => 'Marketplace Commissions', 'percent' => 40, 'color' => '#0E8F57'],
            ['label' => 'Corporate Contracts', 'percent' => 20, 'color' => '#065F46'],
            ['label' => 'Housing Society Contracts', 'percent' => 10, 'color' => '#D4AF37'],
            ['label' => 'Subscription Plans', 'percent' => 10, 'color' => '#7FB8A0'],
            ['label' => 'Facility Management', 'percent' => 8, 'color' => '#0B0D0C'],
            ['label' => 'Featured Listings', 'percent' => 4, 'color' => '#C9A96A'],
            ['label' => 'Advertising', 'percent' => 3, 'color' => '#9AA79E'],
            ['label' => 'Training & Certification', 'percent' => 2, 'color' => '#4E6B5F'],
            ['label' => 'Franchise Fees', 'percent' => 2, 'color' => '#D9D2BE'],
            ['label' => 'SaaS & Analytics (Future)', 'percent' => 1, 'color' => '#BFC7C2'],
        ],
    ]);

$add('professional', 'home', 'Become a Professional', 'Join the network', 'Grow your career with Taskora.', 'with Taskora.',
    'Become part of one of Pakistan\'s fastest-growing professional networks — steady work, weekly payouts and a digital reputation that travels with you.',
    [
        'benefits' => ['More customers', 'Weekly payouts', 'Digital profile', 'Performance rewards', 'Training & certification', 'Flexible hours'],
        'cta_label' => 'Apply to Join', 'cta_href' => 'account/register.php?role=provider',
    ]);

$add('vision', 'home', 'Long-Term Vision', 'Long-term vision',
    "We are building the digital backbone of Pakistan's service economy — not another booking app, but one trusted ecosystem where people discover services, learn, hire professionals and run their businesses.",
    '', 'Taskora is powered by Trovec Technologies.',
    ['legal' => 'Taskora (Pvt.) Ltd. · Registered under SECP · A Trovec Technologies Company']);

$add('cta', 'home', 'Download CTA', 'Scan · Book · Relax', "One app.\nEvery service. Every need.", 'Every need.',
    'Get the Taskora app and keep a trusted professional just a tap away.',
    [
        'buttons' => [
            ['label' => 'Download for iOS', 'href' => '#', 'variant' => 'ios'],
            ['label' => 'Download for Android', 'href' => '#', 'variant' => 'android'],
            ['label' => 'Talk to Sales', 'href' => 'contact.php', 'variant' => 'ghost'],
        ],
    ]);

/* ================= CATALOGUE PAGE ================= */
$add('cat_hero', 'catalogue', 'Catalogue — Hero', 'Master professional catalogue', 'Every specialist, verified the same way.', 'verified the same way.',
    'Fifteen major service groups and 300+ professional specialisations — and every single listing clears the same trusted verification framework before a customer sees it.',
    [
        'stats' => [
            ['value' => '15', 'label' => 'Major Service Groups'],
            ['value' => '300+', 'label' => 'Specialisations'],
            ['value' => '16', 'label' => 'Verification Checks'],
            ['value' => '100%', 'label' => 'Profiles Verified'],
        ],
    ]);
$add('cat_verification', 'catalogue', 'Catalogue — Verification', 'Our verification standard', 'Sixteen checks. One standard.', 'One standard.',
    'Whether it is an electrician or a chartered accountant, every professional on Taskora clears the same framework before activation.',
    [
        'checks' => ['CNIC Verification', 'Mobile Verification', 'Email Verification', 'Address Verification', 'Police Clearance (where applicable)', 'Qualification Verification', 'Experience Verification', 'Trade License Verification', 'SECP / NTN Verification', 'Tax Compliance Verification', 'Background Screening', 'Customer Rating System', 'Quality Inspection', 'Insurance Eligibility', 'Digital Service Record', 'Continuous Performance Monitoring'],
    ]);
$add('cat_browse', 'catalogue', 'Catalogue — Browse heading', 'Browse by category', 'The complete catalogue.', 'catalogue.',
    'Open any category to reveal every specialisation inside it.', []);
$add('cat_profile', 'catalogue', 'Catalogue — Sample Profile', 'Verified professional profile', 'What every listing tells you.', 'tells you.',
    'Each field below is captured, checked and kept up to date before anyone can book.',
    [
        'name' => 'Imran Mahmood', 'initials' => 'IM', 'role' => 'HVAC & AC Services — Islamabad',
        'fields' => [
            ['label' => 'Professional ID', 'value' => 'TSK-AC-08421', 'tone' => ''],
            ['label' => 'Category', 'value' => 'HVAC & AC Services', 'tone' => ''],
            ['label' => 'Experience', 'value' => '9 Years', 'tone' => ''],
            ['label' => 'Qualifications', 'value' => 'HVAC Diploma, TEVTA', 'tone' => ''],
            ['label' => 'Languages', 'value' => 'Urdu, English', 'tone' => ''],
            ['label' => 'Service Areas', 'value' => 'Islamabad, Rawalpindi', 'tone' => ''],
            ['label' => 'Customer Rating', 'value' => '4.9 ★ (612 reviews)', 'tone' => 'gold'],
            ['label' => 'Completed Jobs', 'value' => '1,284', 'tone' => ''],
            ['label' => 'Response Time', 'value' => '< 12 min', 'tone' => 'green'],
            ['label' => 'Success Rate', 'value' => '98.4%', 'tone' => 'green'],
            ['label' => 'Warranty Offered', 'value' => '30 Days on Repairs', 'tone' => ''],
            ['label' => 'Emergency Availability', 'value' => 'Available', 'tone' => 'green'],
            ['label' => 'Insurance Status', 'value' => 'Covered', 'tone' => ''],
            ['label' => 'Pricing', 'value' => 'PKR 800 – 2,200 / visit', 'tone' => ''],
            ['label' => 'Digital Service History', 'value' => 'Available on request', 'tone' => ''],
        ],
    ]);
$add('cat_quality', 'catalogue', 'Catalogue — Quality Promise', 'Quality promise', 'What every customer can count on.', 'count on.', '', [
    'items' => ['Verified Professional', 'Transparent Pricing', 'Digital Booking', 'Live Job Tracking', 'Secure Digital Payments', 'Electronic Invoices', 'Warranty (Where Applicable)', '24/7 Customer Support', 'Complaint Resolution', 'Digital Service History', 'Verified Reviews', 'Quality Assurance'],
]);
$add('cat_cta', 'catalogue', 'Catalogue — CTA', '', 'Find the right professional — verified from day one.', 'verified from day one.', '', [
    'primary_label' => 'Book a Service', 'primary_href' => 'forms.php?type=booking',
    'secondary_label' => 'List Your Services', 'secondary_href' => 'account/register.php?role=provider',
]);

/* ================= CATEGORIES (services directory) ================= */
$cats = [
    ['Home Services', "House & Deep Cleaning\nPlumbing & Electrical\nAC Repair & Installation\nCarpentry & Painting\nPest Control & Disinfection", 'Everyday upkeep handled by vetted technicians — cleaning, repairs, cooling and more, scheduled around you.'],
    ['Construction & Renovation', "Grey Structure & Finishing\nRenovation & Remodeling\nWaterproofing & Flooring\nProject Management\nQuantity Surveying & BOQ", 'From grey structure to final finish, with qualified contractors and transparent BOQs at every stage.'],
    ['Architects & Interior Design', "Architectural Design\nInterior & Landscape Design\n3D Rendering & BIM\nStructural & MEP Design\nRenovation Consultancy", 'Registered architects and designers who turn ideas into buildable, beautiful spaces.'],
    ['Legal Services', "Corporate & Contract Law\nProperty & Family Law\nLegal Documentation\nDispute Resolution\nCompany Registration", 'Qualified lawyers for contracts, property, family matters and disputes — clear advice, fair fees.'],
    ['Chartered Accountants & Taxation', "Tax Filing & Advisory\nAudit & Assurance\nSECP Company Registration\nBookkeeping & Payroll\nFinancial Consulting", 'Chartered professionals for tax, audit, registrations and books that stay in order.'],
    ['IT Services & Software Solutions', "Web & App Development\nSoftware & AI Solutions\nCloud & Cybersecurity\nIT Support & Networking\nData Entry & Analytics", 'Engineers and specialists who build, secure and support the technology your business runs on.'],
    ['Tutors & Education', "Home & Online Tutors\nTest Preparation\nLanguage Coaching\nSkill Development\nComputer Training", 'Verified tutors and trainers, at home or online, for every grade, test and skill.'],
    ['Corporate & Business Services', "Business Setup & Consulting\nVirtual Assistance\nCorporate Contracts\nMarket Research\nStrategy Advisory", 'Support for founders and companies — from setup and research to strategy and day-to-day admin.'],
    ['Facility Management', "Building Maintenance\nJanitorial Services\nWaste Management\nHVAC & AMC Contracts\nVendor Management", 'Complete upkeep for offices, plazas and campuses under managed, reportable contracts.'],
    ['HR & Recruitment Services', "Recruitment & Staffing\nPayroll Management\nHR Consulting\nBackground Checks\nTraining & Certification", 'Hire, pay, verify and train your people with experienced HR partners.'],
    ['Digital Marketing & Media', "SEO & Social Media\nGraphic Design\nPhotography & Videography\nBranding & Content\nPaid Advertising", 'Creative and performance teams that grow your brand online and offline.'],
    ['Health & Wellness', "Home Nursing & Caregiving\nFitness Trainers\nBeauty & Salon\nWellness Consulting\nElderly Care", 'Caring, certified professionals for nursing, fitness, beauty and elder care at home.'],
    ['Event Management', "Event Planning & Catering\nPhotography & Décor\nVenue & Vendor Coordination\nCorporate Events\nTicketing & Sponsorship", 'Planners, caterers and creatives who deliver memorable weddings and corporate events.'],
    ['Logistics & Transport', "Packers & Movers\nCourier & Delivery\nVehicle Rentals\nWarehousing\nIntercity Transport", 'Reliable moving, delivery, storage and transport — tracked from pickup to drop-off.'],
    ['Security Services', "CCTV & Alarm Systems\nSecurity Guards\nAccess Control\nFire Safety\nBackground Verification", 'Trained guards and certified installers to protect homes, offices and events.'],
    ['Insurance, Financial & Investment Services', "Insurance Marketplace\nSME Financing\nInvestment Referral (Partner-Only)\nSavings & Gold Plans\nInvoice Financing", 'Introductions to licensed banks, insurers and brokerages — Taskora never holds customer funds.'],
];

/* ================= CATALOGUE GROUPS ================= */
$catalogue = [
    ['Home Services', '10 groups · 74 specialisations', 'green', '', "Electrical Services: Electricians, Wiring Specialists, Distribution Board Installation, Generator Installation, UPS Installation, Smart Home Electrical Systems, Lighting Installation, Emergency Electricians\nPlumbing Services: Plumbers, Water Supply Specialists, Drainage Experts, Leak Detection, Sewerage Repair, Water Tank Installation, Bathroom Plumbing, Kitchen Plumbing\nHVAC & AC Services: AC Installation, AC Repair, AC Maintenance, HVAC Engineers, Ventilation Systems, Chiller Specialists, Duct Cleaning, Refrigeration Technicians\nHome Cleaning: Deep Cleaning, Sofa Cleaning, Carpet Cleaning, Kitchen Cleaning, Bathroom Cleaning, Glass Cleaning, Water Tank Cleaning, Post-Construction Cleaning\nPest Control: Termite Control, General Pest Control, Rodent Control, Mosquito Control, Fumigation Services\nAppliance Repair: Refrigerator, Washing Machine, Microwave, Oven, Dishwasher, Water Dispenser, Small Appliances\nCarpentry: Furniture Repair, Custom Furniture, Wardrobes, Kitchen Cabinets, Wooden Flooring, Door Installation\nPainting Services: Interior Painting, Exterior Painting, Texture Paint, Decorative Paint, Waterproof Coating\nGardening & Landscaping: Garden Design, Lawn Maintenance, Tree Trimming, Irrigation, Plant Supply\nMoving & Shifting: House Moving, Packing, Loading, Unloading, Storage Solutions"],
    ['Construction & Property', '20 specialisations', 'green-dark', '', 'Civil Engineers, Architects, Interior Designers, Structural Engineers, Quantity Surveyors, Project Managers, Building Contractors, Renovation Specialists, Masonry Experts, Tile Installation, Marble & Granite, Aluminum & Glass, Steel Fabrication, Roofing Contractors, Waterproofing, False Ceiling, Flooring Specialists, Property Inspectors, Building Maintenance, Smart Building Automation'],
    ['Property & Real Estate', '10 specialisations', 'black', '', 'Property Consultants, Real Estate Agents, Property Valuation Experts, Property Documentation, Property Verification, Transfer Specialists, Investment Advisors, Facility Managers, Rental Management, Property Marketing'],
    ['Business Services', '10 specialisations', 'gold', '', 'Chartered Accountants, Tax Consultants, Auditors, Bookkeepers, Financial Consultants, Payroll Specialists, Company Registration, Corporate Secretarial Services, Business Licensing, Compliance Consultants'],
    ['Legal Services', '11 specialisations', 'green', '', 'Lawyers, Corporate Lawyers, Property Lawyers, Family Lawyers, Civil Lawyers, Criminal Lawyers, Labour Lawyers, Tax Lawyers, Contract Drafting, Legal Documentation, Arbitration & Mediation'],
    ['HR & Recruitment', '8 specialisations', 'green-dark', '', 'HR Consultants, Recruiters, Payroll Management, Background Verification, Executive Search, Corporate Training, Performance Management, HR Outsourcing'],
    ['IT & Technology', '15 specialisations', 'black', '', 'Software Developers, Web Developers, Mobile App Developers, AI Engineers, Data Scientists, Cybersecurity Specialists, Cloud Engineers, DevOps Engineers, ERP Consultants, CRM Specialists, UI/UX Designers, QA Engineers, Network Engineers, IT Support, Database Administrators'],
    ['Digital Marketing', '14 specialisations', 'gold', '', 'SEO Experts, Social Media Managers, PPC Specialists, Google Ads, Meta Ads, Email Marketing, Content Writers, Copywriters, Graphic Designers, Brand Consultants, Video Editors, Motion Graphics, Photography, Videography'],
    ['Education', '9 specialisations', 'green', '', 'Tutors, Language Teachers, Science Teachers, Mathematics Teachers, Computer Instructors, Quran Tutors, Test Preparation, Online Coaching, Professional Trainers'],
    ['Health & Wellness', '8 specialisations', 'green-dark', '', 'Doctors (Partner Network), Physiotherapists, Nutritionists, Fitness Trainers, Yoga Instructors, Mental Wellness Coaches, Home Nursing, Elder Care'],
    ['Security Services', '7 specialisations', 'black', '', 'Security Guards, CCTV Installation, Access Control, Alarm Systems, Fire Safety, Security Audits, Event Security'],
    ['Logistics & Transport', '7 specialisations', 'gold', '', 'Courier Services, Freight Forwarding, Packers & Movers, Warehousing, Fleet Management, Dispatch Services, Last-Mile Delivery'],
    ['Event Services', '8 specialisations', 'green', '', 'Event Planners, Wedding Planners, Decorators, Caterers, Sound & Lighting, Photography, Videography, Stage Management'],
    ['Corporate Services', '7 specialisations', 'green-dark', '', 'Office Administration, Reception Services, Document Management, Office Maintenance, Office Relocation, Business Process Outsourcing, Virtual Assistants'],
    ['Insurance & Financial Services', 'Partner-based · 16 specialisations', 'black', 'Taskora only introduces customers to licensed banks, insurers and brokerages — it never holds funds or underwrites policies itself.', 'Insurance Advisors, Health Insurance, Life Insurance, Motor Insurance, Property Insurance, SME Insurance, Takaful Services, Savings Plans, Budget Planning, Gold Savings, Retirement Planning, Mutual Fund Referrals, Investment Referrals, SME Financing, Invoice Financing, Freelancer Banking Solutions'],
];

/* ================= TESTIMONIALS (samples) ================= */
$testimonials = [
    ['Our main breaker tripped late at night. The app found an electrician nearby and the fault was fixed within the hour — calm, polite and tidy.', 'Sample review', 'DHA, Lahore', 5],
    ['We moved facility management for two office floors onto one dashboard. Fewer vendors to chase and every request is logged.', 'Sample review — Office Manager', 'Karachi', 5],
    ['Residents now raise maintenance requests themselves and can see the status. Our committee meetings got a lot shorter.', 'Sample review — Society Committee', 'Islamabad', 4],
    ['The cost estimator was close to the final quote, and the painter shared an itemised invoice for every material used.', 'Sample review — Homeowner', 'Rawalpindi', 5],
    ['Booked a deep clean before Eid on a Thursday evening. The team arrived on time Saturday morning with their own equipment.', 'Sample review — Apartment Resident', 'Faisalabad', 5],
];

/* ================= SETTINGS ================= */
$settings = [
    // key, value, label, group, input
    ['site_name', 'Taskora', 'Site name', 'general', 'text'],
    ['site_tagline', 'One App. Every Service. Every Need.', 'Tagline', 'general', 'text'],
    ['powered_by', 'Powered by Trovec Technologies', 'Powered-by line', 'general', 'text'],
    ['logo', '', 'Logo image (leave empty to use built-in logo)', 'general', 'image'],
    ['favicon', '', 'Favicon image', 'general', 'image'],
    ['phone', '+92 300 0000000', 'Phone number', 'contact', 'text'],
    ['whatsapp', '923000000000', 'WhatsApp number (digits only, with country code)', 'contact', 'text'],
    ['email', 'info@taskora.digital', 'Email address', 'contact', 'text'],
    ['address', 'Islamabad, Pakistan', 'Office address', 'contact', 'textarea'],
    ['hours', 'Support available 24/7 · Office: Mon–Sat, 9:00 AM – 6:00 PM', 'Working hours', 'contact', 'text'],
    ['map_embed', '', 'Google Maps embed URL (optional)', 'contact', 'text'],
    ['cities', "Islamabad\nRawalpindi\nLahore\nKarachi\nFaisalabad\nPeshawar\nMultan", 'Service cities (one per line)', 'contact', 'textarea'],
    ['facebook', '#', 'Facebook URL', 'social', 'text'],
    ['instagram', '#', 'Instagram URL', 'social', 'text'],
    ['linkedin', '#', 'LinkedIn URL', 'social', 'text'],
    ['twitter', '#', 'X / Twitter URL', 'social', 'text'],
    ['youtube', '', 'YouTube URL', 'social', 'text'],
    ['app_ios', '#', 'App Store link', 'social', 'text'],
    ['app_android', '#', 'Google Play link', 'social', 'text'],
    ['meta_title', 'Taskora — Book Verified Home, Business & Property Services in Pakistan', 'Default SEO title', 'seo', 'text'],
    ['meta_description', 'Taskora is Pakistan\'s all-in-one services app. Book verified electricians, cleaners, AC technicians, lawyers, accountants, architects and 100+ more services with live tracking and secure payments.', 'Default SEO description', 'seo', 'textarea'],
    ['meta_keywords', 'home services Pakistan, verified professionals, book electrician Islamabad, AC service, deep cleaning, facility management', 'SEO keywords', 'seo', 'text'],
    ['seo_keyword', 'home services in Pakistan', 'Main SEO keyword', 'seo', 'text'],
    ['google_analytics', '', 'Google Analytics ID (G-XXXX)', 'seo', 'text'],
    ['footer_about', 'Pakistan\'s digital home for trusted, verified home, business and property services — all in a single app.', 'Footer about text', 'footer', 'textarea'],
    ['copyright', '© {year} Taskora (Pvt.) Ltd. · A Trovec Technologies Company', 'Copyright line ({year} is replaced)', 'footer', 'text'],
];

/* ================= PAGES ================= */
$pages = [
    ['about', 'About Taskora', 'Learn about Taskora, Pakistan\'s connected ecosystem for verified home, business and property services.',
        "<h2>Our story</h2><p>Taskora began with a simple frustration: finding a dependable professional in Pakistan usually meant a chain of phone calls, vague quotes and no way to follow up. We set out to replace that with one calm, transparent experience.</p><h2>What we do</h2><p>Taskora connects households, housing societies and businesses with verified professionals across sixteen service categories — from deep cleaning and AC repair to architecture, legal and accounting support. Every job can be estimated, booked, tracked, paid for and reviewed in one place.</p><h2>How we work</h2><ul><li><strong>Verification first</strong> — professionals are screened before activation.</li><li><strong>Clear pricing</strong> — quotations are shared and agreed before work starts.</li><li><strong>Accountability</strong> — every completed job is rated, and ratings shape who gets matched next.</li></ul><h2>Who we are</h2><p>Taskora (Pvt.) Ltd. is registered with the SECP and is a Trovec Technologies company.</p>"],
    ['privacy', 'Privacy Policy', 'How Taskora collects, uses and protects your personal information.',
        "<p>This policy explains what information Taskora collects, why we collect it and the choices you have.</p><h2>Information we collect</h2><ul><li>Account details such as your name, phone number, email and city.</li><li>Booking details including address, service requirements and preferred dates.</li><li>For professionals: identity, qualification and experience information used for verification.</li><li>Basic technical data such as browser type and IP address for security.</li></ul><h2>How we use it</h2><p>We use your information to match bookings with professionals, communicate about your jobs, process payments, verify providers, resolve complaints and improve our service. We do not sell personal data.</p><h2>Sharing</h2><p>Relevant booking details are shared with the professional assigned to your job. Financial products are provided by licensed partners who apply their own privacy terms.</p><h2>Security</h2><p>Passwords are stored using one-way hashing and access to personal data is restricted to authorised staff.</p><h2>Your rights</h2><p>You may request a copy, correction or deletion of your personal data by contacting us using the details on our Contact page.</p>"],
    ['terms', 'Terms & Conditions', 'The terms that apply when you use Taskora as a customer or a professional.',
        "<p>By using Taskora you agree to these terms. Please read them carefully.</p><h2>The platform</h2><p>Taskora is a marketplace that connects customers with independent, verified professionals. The professional is responsible for performing the service; Taskora facilitates booking, communication, payment and dispute resolution.</p><h2>Bookings & pricing</h2><p>Estimates shown on the website are indicative. The final price is agreed through a quotation before work starts. Once a customer accepts a quotation, the deal is locked.</p><h2>Cancellations & refunds</h2><p>Bookings can be cancelled before work starts. Refund requests are reviewed within three business days using the Refund Request form.</p><h2>Professional conduct</h2><p>Professionals must keep their profile accurate, honour agreed quotations and maintain respectful conduct. Accounts may be suspended for repeated low ratings or policy breaches.</p><h2>Financial services</h2><p>Taskora does not hold customer investments. Insurance and financial products are delivered exclusively by licensed partners.</p><h2>Liability</h2><p>To the extent permitted by law, Taskora's liability is limited to the platform fees paid for the affected booking.</p>"],
];

return compact('sections', 'cats', 'catalogue', 'testimonials', 'settings', 'pages');
