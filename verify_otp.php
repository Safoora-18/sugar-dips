<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';
$otp   = $data['otp'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE phone=?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['success'=>false,'message'=>'User not found']);
    exit;
}

if ($user['otp'] !== $otp) {
    echo json_encode(['success'=>false,'message'=>'Invalid OTP']);
    exit;
}

if ((time() * 1000) > $user['otp_expires']) {
    echo json_encode(['success'=>false,'message'=>'OTP expired']);
    exit;
}

// Clear OTP
$conn->query("UPDATE users SET otp=NULL, otp_expires=NULL WHERE phone='$phone'");

$isNew = empty($user['name']);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_phone'] = $phone;
$_SESSION['user_name'] = $user['name'] ?? '';

echo json_encode(['success'=>true,'isNew'=>$isNew,'user'=>['id'=>$user['id'],'name'=>$user['name'],'phone'=>$phone]]);
?>