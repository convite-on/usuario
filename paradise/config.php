<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/upsell-values.php'; // <-- seu arquivo de valores

$upsell = $_GET['upsell'] ?? 'up1';
$value = getUpsellValue($upsell);

if ($value === null) {
    echo json_encode([
        'success' => false,
        'error' => 'Upsell inválido'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'upsell' => $upsell,
    'value' => $value, // float com centavos
    'formatted' => 'R$ ' . number_format($value, 2, ',', '.')
]);
