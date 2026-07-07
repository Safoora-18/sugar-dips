<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';

if (strlen($phone) !== 10) {
    echo json_encode(['success'=>false,'message'=>'Invalid phone']);
    exit;
}

$otp = strval(rand(100000, 999999));
$expires = (time() + 300) * 1000; // 5 mins in ms

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE phone=?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt2 = $conn->prepare("UPDATE users SET otp=?, otp_expires=? WHERE phone=?");
    $stmt2->bind_param("sis", $otp, $expires, $phone);
    $stmt2->execute();
} else {
    $stmt2 = $conn->prepare("INSERT INTO users (phone, otp, otp_expires) VALUES (?,?,?)");
    $stmt2->bind_param("sis", $phone, $otp, $expires);
    $stmt2->execute();
}

// In production: send SMS via MSG91/Twilio
// For demo: return OTP
echo json_encode(['success'=>true,'otp'=>$otp]);
?>