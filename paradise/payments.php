<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');

// ================= CONFIG =================
$API_URL = 'https://multi.paradisepags.com/api/v1/transaction.php';
$API_KEY = 'sk_2d08e24064aa451e4911685e55532a7b8a9d07cae11bd1f8e18dcad050defde8';
$PRODUCT_HASH = 'prod_2311619699e4d252';

$dbFile = __DIR__ . '/transactions.json';

// ================= INPUT =================
$input = json_decode(file_get_contents('php://input'), true);

// ================= VERIFICAÇÃO DE STATUS (GET) =================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['transactionId'])) {
    $db = file_exists($dbFile)
        ? json_decode(file_get_contents($dbFile), true)
        : [];

    $tid = $_GET['transactionId'];

    if (!isset($db[$tid])) {
        echo json_encode(['status' => 'NOT_FOUND']);
        exit;
    }

    echo json_encode([
        'status' => $db[$tid]['status'],
        'upsell' => $db[$tid]['upsell']
    ]);
    exit;
}

// ================= CRIA PIX (POST) =================
if (!isset($input['value']) || $input['value'] <= 0) {
    echo json_encode(['success' => false, 'error' => 'Valor inválido']);
    exit;
}

// ================= FUNÇÕES =================
function randomName() {
    $names = ['Carlos','João','Pedro','Lucas','Rafael'];
    $last  = ['Silva','Santos','Oliveira','Costa'];
    return $names[array_rand($names)] . ' ' . $last[array_rand($last)];
}
function randomEmail($name) {
    return strtolower(str_replace(' ', '.', $name)) . rand(100,9999) . '@gmail.com';
}
function randomPhone() {
    return '11' . rand(900000000, 999999999);
}
function randomCPF() {
    return str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
}
function generateReference() {
    return 'UPSELL-' . time() . '-' . bin2hex(random_bytes(4));
}

// ================= VALOR CORRETO (CENTAVOS) =================
$value = floatval(str_replace(',', '.', $input['value']));
$amount = (int) round($value * 100);

// ================= PAYLOAD =================
$name = randomName();
$reference = generateReference();

$payload = [
    'amount' => $amount,
    'description' => $input['productName'],
    'reference' => $reference,
    'productHash' => $PRODUCT_HASH,
    'customer' => [
        'name' => $name,
        'email' => randomEmail($name),
        'phone' => randomPhone(),
        'document' => randomCPF()
    ]
];

// ================= REQUEST API =================
$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: ' . $API_KEY
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode !== 200 || !isset($result['transaction_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao gerar PIX',
        'debug' => $result
    ]);
    exit;
}

// ================= SALVA NO JSON =================
$db = file_exists($dbFile)
    ? json_decode(file_get_contents($dbFile), true)
    : [];

$db[$result['transaction_id']] = [
    'status' => 'PENDING',
    'upsell' => 'https://calcularroi.shop/BONUS/up1/',
    'amount' => $value,
    'reference' => $reference,
    'created_at' => date('Y-m-d H:i:s')
];

file_put_contents($dbFile, json_encode($db, JSON_PRETTY_PRINT));

// ================= RESPONSE =================
echo json_encode([
    'success' => true,
    'transactionId' => $result['transaction_id'],
    'pixCode' => $result['qr_code'],
    'paymentInfo' => [
        'base64QrCode' => $result['qr_code_base64'],
        'expires_at' => $result['expires_at']
    ]
]);
