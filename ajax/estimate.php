<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
if (!is_post()) json_out(['ok' => false], 405);

$calc = section('calculator');
$d = $calc ? $calc['data'] : [];

$svc = null;
foreach (g($d, 'service_types') as $s) if ($s['id'] === post('service')) $svc = $s;
$freq = null;
foreach (g($d, 'frequencies') as $f) if ($f['id'] === post('frequency')) $freq = $f;
if (!$svc) json_out(['ok' => false, 'message' => 'Unknown service'], 422);
if (!$freq) $freq = ['label' => 'One-Time', 'factor' => 1];

$min = (int)g($d, 'size_min', 3);
$max = (int)g($d, 'size_max', 20);
$size = max($min, min($max, (int)post('size', g($d, 'size_default', 5))));

// Size factor: 5 Marla = 1.0, each extra Marla adds ~9%, smaller homes scale down gently.
$sizeFactor = 0.55 + $size * 0.09;
$base = (float)$svc['base_price'] * $sizeFactor * (float)$freq['factor'];

$low  = round($base * 0.9 / 50) * 50;
$high = round($base * 1.25 / 50) * 50;

json_out([
    'ok' => true,
    'low' => $low,
    'high' => $high,
    'summary' => $svc['label'] . ' · ' . $size . ' ' . g($d, 'size_unit', 'Marla') . ' · ' . $freq['label'],
]);
