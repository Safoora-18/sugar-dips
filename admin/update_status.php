<?php
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success'=>false,'message'=>'Not authorized']);
    exit;
}
require '../db.php';
header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success'=>false,'message'=>'Invalid data']);
    exit;
}

$id     = (int)($data['id']     ?? 0);
$status = trim($data['status']  ?? '');

$allowed = ['pending','confirmed','delivered','cancelled'];
if (!in_array($status, $allowed)) {
    echo json_encode(['success'=>false,'message'=>'Invalid status']);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

echo json_encode(['success'=>true]);
?>