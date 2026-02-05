<?php
header('Content-Type: application/json');

$dbFile = __DIR__ . '/transactions.json';

$payload = json_decode(file_get_contents('php://input'), true);

// Log opcional (debug)
file_put_contents(__DIR__ . '/webhook.log', json_encode($payload) . PHP_EOL, FILE_APPEND);

// Validação mínima
if (!isset($payload['transaction_id'], $payload['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

$transactionId = $payload['transaction_id'];
$status = strtolower($payload['status']); // approved, pending...

$db = file_exists($dbFile)
    ? json_decode(file_get_contents($dbFile), true)
    : [];

if (!isset($db[$transactionId])) {
    http_response_code(404);
    echo json_encode(['error' => 'Transação não encontrada']);
    exit;
}

// Atualiza status
if ($status === 'approved') {
    $db[$transactionId]['status'] = 'APPROVED';
}
elseif ($status === 'failed') {
    $db[$transactionId]['status'] = 'FAILED';
}
elseif ($status === 'refunded') {
    $db[$transactionId]['status'] = 'REFUNDED';
}

file_put_contents($dbFile, json_encode($db, JSON_PRETTY_PRINT));

// ⚠️ OBRIGATÓRIO
http_response_code(200);
echo json_encode(['success' => true]);
