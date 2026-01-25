<?php
// create_session.php

// 1. ALLOW ACCESS FROM YOUR WEBSITE (CORS)
header("Access-Control-Allow-Origin: https://testv11.oneapp.dev");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle "Preflight" check from browser
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. CASHFREE CONFIGURATION
// ---------------------------------------------------------
// REPLACE THESE WITH YOUR KEYS
$clientId = "221824db5358d75f20a38abd628122";      
$clientSecret = "cfsk ma test c2396f942ba6b54192c36e27acef6ed9 1bb935c6"; 

// MODE: Change this to "PRODUCTION" when you go live
$mode = "SANDBOX"; 
// ---------------------------------------------------------

if ($mode === "PRODUCTION") {
    $env_url = "https://api.cashfree.com/pg/orders";
} else {
    $env_url = "https://sandbox.cashfree.com/pg/orders";
}

// 3. GET INPUT DATA
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['orderId']) || !isset($input['amount']) || !isset($input['phone'])) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

// 4. PREPARE DATA FOR CASHFREE
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
        // This is the file that closes itself automatically
        "return_url" => "https://your-app-name.onrender.com/payment_success.html?order_id={order_id}"
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

// 6. OUTPUT RESPONSE
echo $response;
?>
