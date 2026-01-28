<?php
// create_session.php

// 1. ALLOW ACCESS (CORS)
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

// 2. CONFIGURATION (SECURE)
// We get keys from Render Environment Variables
$clientId = getenv('CASHFREE_APP_ID');      
$clientSecret = getenv('CASHFREE_SECRET_KEY'); 

// SAFETY CHECK: If keys are missing in Render, warn the developer
if (!$clientId || !$clientSecret) {
    echo json_encode(["status" => "error", "message" => "Server Config Error: API Keys not set in Render Environment."]);
    exit();
}

// MODE: Change to "PRODUCTION" when you go live
$mode = "SANDBOX"; 

if ($mode === "PRODUCTION") {
    $env_url = "https://api.cashfree.com/pg/orders";
} else {
    $env_url = "https://sandbox.cashfree.com/pg/orders";
}

// 3. READ INPUT
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input) {
    echo json_encode(["status" => "error", "message" => "Empty data received."]);
    exit();
}

// 4. MAP INPUTS
$phone = $input['phone'] ?? $input['customerPhone'] ?? null;
$name  = $input['name']  ?? $input['customerName']  ?? "Customer";
$email = $input['email'] ?? "guest@copycut.com";
$orderId = $input['orderId'] ?? null;
$amount = $input['amount'] ?? null;

// Validate
$missing = [];
if (!$orderId) $missing[] = 'orderId';
if (!$amount) $missing[] = 'amount';
if (!$phone) $missing[] = 'phone';

if (!empty($missing)) {
    echo json_encode([
        "status" => "error", 
        "message" => "Missing fields: " . implode(", ", $missing)
    ]);
    exit();
}

// 5. PREPARE DATA FOR CASHFREE
$orderData = [
    "order_amount" => $amount,
    "order_currency" => "INR",
    "order_id" => $orderId,
    "customer_details" => [
        "customer_id" => "CUST_" . preg_replace('/\D/', '', $phone),
        "customer_phone" => $phone,
        "customer_name" => $name,
        "customer_email" => $email
    ],
    "order_meta" => [
        // Make sure this URL matches your Render App Name
        "return_url" => "https://copycut-backend.onrender.com/payment_success.html?order_id={order_id}"
    ]
];

// 6. SEND TO CASHFREE
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
if(curl_errno($ch)){
    echo json_encode(["status" => "error", "message" => 'Curl error: ' . curl_error($ch)]);
    exit();
}
curl_close($ch);

echo $response;
?>