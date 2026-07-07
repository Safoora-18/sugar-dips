<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data   = json_decode(file_get_contents('php://input'), true);
$amount = (int)($data['amount'] ?? 0);

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit;
}

// ── Razorpay credentials ──────────────────────────────────────────────
// Sign up at razorpay.com → Settings → API Keys → Generate Test Key
$keyId     = 'rzp_test_SmxqykgpsFsd0e';   // ← replace with your key
$keySecret = '3UqbZV2hbdQuzoCfG6QEe1XD';        // ← replace with your secret

$payload = json_encode([
    'amount'   => $amount * 100,  // Razorpay works in paise (1 Rs = 100 paise)
    'currency' => 'INR',
    'receipt'  => 'order_' . time(),
]);

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_USERPWD        => "$keyId:$keySecret",
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!empty($response['id'])) {
    echo json_encode([
        'success'  => true,
        'order_id' => $response['id'],
        'amount'   => $amount,
        'key_id'   => $keyId,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Could not create Razorpay order. Check API keys.',
    ]);
}
?>