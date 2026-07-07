<?php
session_start();
require 'db.php';
$conn->set_charset('utf8mb4');
header('Content-Type: application/json');

$data  = json_decode(file_get_contents('php://input'), true);
$phone = trim($data['phone'] ?? '');
$uid   = trim($data['uid']   ?? '');

if (!$phone) {
    echo json_encode(['success'=>false,'message'=>'Phone required']);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE phone=?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    // Existing user
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_phone'] = $user['phone'];
    $_SESSION['user_name']  = $user['name'] ?? '';

    echo json_encode([
        'success' => true,
        'isNew'   => empty($user['name']),
        'user'    => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'phone' => $phone
        ]
    ]);
} else {
    // New user — insert
    $stmt2 = $conn->prepare("INSERT INTO users (phone) VALUES (?)");
    $stmt2->bind_param("s", $phone);
    $stmt2->execute();
    $newId = $conn->insert_id;

    $_SESSION['user_id']    = $newId;
    $_SESSION['user_phone'] = $phone;
    $_SESSION['user_name']  = '';

    echo json_encode([
        'success' => true,
        'isNew'   => true,
        'user'    => ['id'=>$newId,'phone'=>$phone]
    ]);
}
?>