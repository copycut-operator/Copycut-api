<?php
// verify_status.php

header("Access-Control-Allow-Origin: https://testv11.oneapp.dev/");
header("Content-Type: application/json");

// 1. CONFIGURATION
// ---------------------------------------------------------
$clientId = "221824db5358d75f20a38abd628122";      
$clientSecret = "cfsk ma test c2396f942ba6b54192c36e27acef6ed9 1bb935c6"; 

$mode = "SANDBOX"; // Change to "PRODUCTION" for live
// ---------------------------------------------------------

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
