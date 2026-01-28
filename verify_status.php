<?php
// verify_status.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 1. CONFIGURATION (SECURE)
$clientId = getenv('CASHFREE_APP_ID');      
$clientSecret = getenv('CASHFREE_SECRET_KEY'); 

$mode = "SANDBOX"; 

if (!isset($_GET['order_id'])) {
    echo json_encode(["status" => "error", "message" => "Order ID missing"]);
    exit();
}

$orderId = $_GET['order_id'];

if ($mode === "PRODUCTION") {
    $env_url = "https://api.cashfree.com/pg/orders/" . $orderId;
} else {
    $env_url = "https://sandbox.cashfree.com/pg/orders/" . $orderId;
}

// 2. ASK CASHFREE FOR STATUS
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $env_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "x-api-version: 2023-08-01",
    "x-client-id: " . $clientId,
    "x-client-secret: " . $clientSecret
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>