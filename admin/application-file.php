<?php
/** Streams an applicant document to a signed-in admin (the folder itself is blocked from the web). */
require __DIR__ . '/_layout.php';
require_once dirname(__DIR__) . '/includes/pro_registration.php';
require_admin();

$a = row('SELECT ref, documents FROM provider_applications WHERE id = ?', [(int)get('id')]);
$want = get('f');
$found = null;
if ($a) {
    foreach (json_decode($a['documents'] ?: '{}', true) ?: [] as $doc) {
        foreach ($doc['files'] ?? [] as $f) if ($f['file'] === $want) $found = $f;
    }
}
$path = $found ? pro_doc_dir($a['ref']) . '/' . basename($found['file']) : '';
if (!$found || !is_file($path)) { http_response_code(404); exit('File not found.'); }

$types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf'];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="' . str_replace(['"', "\r", "\n"], '', $found['name']) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($path);
