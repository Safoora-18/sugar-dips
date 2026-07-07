<?php
session_start();
header('Content-Type: application/json');

$data   = json_decode(file_get_contents('php://input'), true);
$code   = strtoupper(trim($data['code'] ?? ''));
$total  = (int)($data['total'] ?? 0);

// Define your coupon codes here
$coupons = [
    'SWEET10'  => ['type' => 'percent', 'value' => 10,  'desc' => '10% off'],
    'FLAT50'   => ['type' => 'flat',    'value' => 50,  'desc' => '₹50 off'],
    'SUGAR20'  => ['type' => 'percent', 'value' => 20,  'desc' => '20% off'],
    'WELCOME'  => ['type' => 'flat',    'value' => 100, 'desc' => '₹100 off'],
    'DIPS15'   => ['type' => 'percent', 'value' => 15,  'desc' => '15% off'],
];
if (!isset($coupons[$code])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid coupon code'
    ]);
    exit;
}
$coupon   = $coupons[$code];
$discount = 0;
if ($coupon['type'] === 'percent') {
    $discount = (int)round($total * $coupon['value'] / 100);
} else {
    $discount = min($coupon['value'], $total);
}
$newTotal = $total - $discount;
echo json_encode([
    'success'   => true,
    'discount'  => $discount,
    'newTotal'  => $newTotal,
    'desc'      => $coupon['desc'],
    'message'   => '🎉 Coupon applied! ' . $coupon['desc']
]);
?>