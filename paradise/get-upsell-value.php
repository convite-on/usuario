<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// ================= CONFIG =================
$UPSELLS = [
    'up0' => [
        'value' => 19.36,
        'formatted' => 'R$ 19,36'
    ],
    'up1' => [
        'value' => 20,55,
        'formatted' => 'R$ 20,55'
    ],
    'up2' => [
        'value' => 29.90,
        'formatted' => 'R$ 29,90'
    ],
    'up3' => [
        'value' => 49.90,
        'formatted' => 'R$ 49,90'
    ]
];
// =========================================

$upsell = $_GET['upsell'] ?? 'up1';

if (!isset($UPSELLS[$upsell])) {
    echo json_encode([
        'success' => false,
        'error' => 'Upsell inválido'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'upsell' => $upsell,
    'value' => $UPSELLS[$upsell]['value'],
    'formatted' => $UPSELLS[$upsell]['formatted']
]);
