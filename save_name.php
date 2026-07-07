<?php
session_start();
require 'db.php';
$conn->set_charset('utf8mb4');
header('Content-Type: application/json');

$data  = json_decode(file_get_contents('php://input'), true);
$phone = trim($data['phone'] ?? '');
$name  = trim($data['name']  ?? '');

if (!$phone || !$name) {
    echo json_encode(['success'=>false,'message'=>'Missing data']);
    exit;
}

// Update name in DB
$stmt = $conn->prepare("UPDATE users SET name=? WHERE phone=?");
$stmt->bind_param("ss", $name, $phone);
$stmt->execute();

// Update session
$_SESSION['user_name'] = $name;

// Get user id for session
$stmt2 = $conn->prepare("SELECT id FROM users WHERE phone=?");
$stmt2->bind_param("s", $phone);
$stmt2->execute();
$user = $stmt2->get_result()->fetch_assoc();

if ($user) {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_phone'] = $phone;
}

echo json_encode(['success'=>true,'name'=>$name]);
?>