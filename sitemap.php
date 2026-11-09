<?php
require __DIR__ . '/includes/bootstrap.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$root = $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$urls = [
    ['index.php', '1.0', 'weekly'], ['catalogue.php', '0.9', 'weekly'], ['contact.php', '0.7', 'monthly'],
    ['forms.php?type=booking', '0.8', 'monthly'], ['forms.php?type=company', '0.6', 'monthly'],
    ['account/register.php?role=provider', '0.6', 'monthly'],
];
foreach (rows('SELECT slug FROM categories WHERE is_active = 1 ORDER BY sort_order') as $c) $urls[] = ['service.php?slug=' . $c['slug'], '0.8', 'weekly'];
foreach (rows('SELECT slug FROM pages WHERE is_active = 1') as $p) $urls[] = ['page.php?slug=' . $p['slug'], '0.4', 'yearly'];

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo '  <url><loc>' . htmlspecialchars($root . base_url($u[0]), ENT_XML1) . '</loc><changefreq>' . $u[2] . '</changefreq><priority>' . $u[1] . "</priority></url>\n";
}
echo '</urlset>';
