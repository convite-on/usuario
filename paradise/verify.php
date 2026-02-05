<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');

$dbFile = __DIR__ . '/transactions.json';

$transactionId = $_GET['transactionId'] ?? null;

// fallback caso não venha redirect (boa prática)
$defaultRedirect = 'https://calcularroi.shop/BONUS/up1/';
$redirect = $_GET['redirect'] ?? $defaultRedirect;

if (!$transactionId) {
    echo json_encode([
        'status' => 'ERROR',
        'error' => 'transactionId ausente'
    ]);
    exit;
}

$db = file_exists($dbFile)
    ? json_decode(file_get_contents($dbFile), true)
    : [];

if (!isset($db[$transactionId])) {
    echo json_encode([
        'status' => 'NOT_FOUND'
    ]);
    exit;
}

// STATUS ATUAL
$status = strtoupper($db[$transactionId]['status']);

if ($status === 'APPROVED') {
    echo json_encode([
        'status' => 'APPROVED',
        'redirect' => $redirect
    ]);
    exit;
}

// Opcional: tratar falhas
if (in_array($status, ['FAILED', 'REFUNDED'])) {
    echo json_encode([
        'status' => $status
    ]);
    exit;
}

// Default
echo json_encode([
    'status' => 'PENDING'
]);
