<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$keySecret = 'YOUR_KEY_SECRET'; // ← same secret as create_order.php

$rpOrderId  = $data['razorpay_order_id']   ?? '';
$rpPayId    = $data['razorpay_payment_id']  ?? '';
$rpSig      = $data['razorpay_signature']   ?? '';
$dbOrderId  = (int)($data['order_id']       ?? 0);

// Verify Razorpay signature
$expected = hash_hmac('sha256', $rpOrderId . '|' . $rpPayId, $keySecret);

if (!hash_equals($expected, $rpSig)) {
    echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
    exit;
}

// Mark order as paid
$stmt = $conn->prepare(
    "UPDATE orders SET payment_status='paid', payment_id=? WHERE id=? AND user_id=?"
);
$userId = $_SESSION['user_id'];
$stmt->bind_param('sii', $rpPayId, $dbOrderId, $userId);
$stmt->execute();

echo json_encode(['success' => true]);
?>