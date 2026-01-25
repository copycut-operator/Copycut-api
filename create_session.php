<?php
// create_session.php
// DIAGNOSTIC VERSION

// 1. CORS
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

header("Content-Type: application/json");

// 2. CONFIGURATION
$clientId = "221824db5358d75f20a38abd628122";      
$clientSecret = "cfsk ma test c2396f942ba6b54192c36e27acef6ed9 1bb935c6"; 
$env_url = "https://sandbox.cashfree.com/pg/orders";

// 3. READ INPUT
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// --- DIAGNOSTIC CHECK ---
// If input is empty, tell the user exactly what the raw input was
if (!$input) {
    echo json_encode([
        "status" => "error", 
        "message" => "Server received empty data. Raw Input: '" . $rawInput . "'"
    ]);
    exit();
}

// Check missing fields
$missing = [];
if (!isset($input['orderId'])) $missing[] = 'orderId';
if (!isset($input['amount'])) $missing[] = 'amount';
if (!isset($input['phone'])) $missing[] = 'phone';

if (!empty($missing)) {
    // Show which specific fields are missing and what was actually received
    echo json_encode([
        "status" => "error", 
        "message" => "Missing fields: " . implode(", ", $missing) . ". Received: " . json_encode($input)
    ]);
    exit();
}
// ------------------------

// 4. PREPARE DATA
$orderData = [
    "order_amount" => $input['amount'],
    "order_currency" => "INR",
    "order_id" => $input['orderId'],
    "customer_details" => [
        "customer_id" => "CUST_" . preg_replace('/\D/', '', $input['phone']),
        "customer_phone" => $input['phone'],
        "customer_name" => $input['name'] ?? "Customer",
        "customer_email" => $input['email'] ?? "guest@copycut.com"
    ],
    "order_meta" => [
        "return_url" => "https://copycut-backend.onrender.com/payment_success.html?order_id={order_id}"
    ]
];

// 5. SEND TO CASHFREE
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $env_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "x-api-version: 2023-08-01",
    "x-client-id: " . $clientId,
    "x-client-secret: " . $clientSecret,
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
