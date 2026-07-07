<?php
session_start();
require 'db.php';
$conn->set_charset('utf8mb4');
header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success'=>false,'message'=>'Invalid data']);
    exit;
}

$name          = trim($data['name']          ?? '');
$phone         = trim($data['phone']         ?? '');
$address       = trim($data['address']       ?? '');
$date          = trim($data['date']          ?? '');
$time = trim($data['time_slot'] ?? '');
$notes         = trim($data['notes']         ?? '');
$items         = $data['items']              ?? [];
$coupon_code   = trim($data['coupon_code']   ?? '');
$discount      = (int)($data['discount']     ?? 0);
$userId        = $_SESSION['user_id']        ?? null;

if (!$name || !$phone || !$address || !$date) {
    echo json_encode(['success'=>false,'message'=>'Please fill all required fields']);
    exit;
}

if (empty($items)) {
    echo json_encode(['success'=>false,'message'=>'Cart is empty']);
    exit;
}

// Calculate total from items
$total = 0;
foreach ($items as $item) {
    $total += (int)($item['price'] ?? 0) * (int)($item['qty'] ?? 1);
}

// Apply discount
$finalTotal = max(0, $total - $discount);

try {
    $stmt = $conn->prepare(
        "INSERT INTO orders
         (user_id, customer_name, phone, address, delivery_date, delivery_time, notes, coupon_code, discount, total)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "isssssssii",
        $userId, $name, $phone, $address,
        $date, $time, $notes,
        $coupon_code, $discount, $finalTotal
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to save order: " . $stmt->error);
    }

    $orderId = $conn->insert_id;

    foreach ($items as $item) {
        $pname  = $item['name']          ?? '';
        $price  = (int)($item['price']   ?? 0);
        $qty    = (int)($item['qty']     ?? 1);
        $custom = $item['customization'] ?? '';

        $stmt2 = $conn->prepare(
            "INSERT INTO order_items (order_id, product_name, price, qty, customization)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt2->bind_param("isiss", $orderId, $pname, $price, $qty, $custom);
        if (!$stmt2->execute()) {
            throw new Exception("Failed to save item: " . $stmt2->error);
        }
    }

    $_SESSION['order_data'] = [
        'orderId'     => $orderId,
        'name'        => $name,
        'phone'       => $phone,
        'address'     => $address,
        'date'        => $date,
        'time'        => $time,
        'notes'       => $notes,
        'coupon_code' => $coupon_code,
        'discount'    => $discount,
        'items'       => $items,
        'total'       => $finalTotal,
    ];

    $_SESSION['cart'] = [];

    echo json_encode(['success'=>true, 'orderId'=>$orderId, 'total'=>$finalTotal]);

} catch (Exception $e) {
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
?>