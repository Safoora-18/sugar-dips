<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode([
        'success' => false
    ]);
    exit;
}

$action = $data['action'] ?? '';

/* ─────────────────────────────────────────────
   ADD TO CART
───────────────────────────────────────────── */
if ($action === 'add') {

    $id            = $data['id']            ?? 0;
    $name          = $data['name']          ?? '';
    $price         = (int)($data['price']   ?? 0);
    $image         = $data['image']         ?? '';
    $customization = $data['customization'] ?? '';
    $qty           = (int)($data['qty']     ?? 1);

    // Unique key
    $key = $id . '-' . md5($name . $customization);

    // If already exists → increase qty
    if (isset($_SESSION['cart'][$key])) {

        $_SESSION['cart'][$key]['qty'] += $qty;

    } else {

        $_SESSION['cart'][$key] = [
            'id'            => $id,
            'name'          => $name,
            'price'         => $price,
            'image'         => $image,
            'customization' => $customization,
            'qty'           => $qty,
        ];
    }
}

/* ─────────────────────────────────────────────
   REMOVE ITEM
───────────────────────────────────────────── */
if ($action === 'remove') {

    $key = $data['key'] ?? '';

    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }
}

/* ─────────────────────────────────────────────
   UPDATE QUANTITY
───────────────────────────────────────────── */
if ($action === 'update') {

    $key = $data['key'] ?? '';
    $qty = (int)($data['qty'] ?? 0);

    if ($qty < 1) {

        unset($_SESSION['cart'][$key]);

    } else {

        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] = $qty;
        }
    }
}

/* ─────────────────────────────────────────────
   CLEAR CART
───────────────────────────────────────────── */
if ($action === 'clear') {

    $_SESSION['cart'] = [];
}

/* ─────────────────────────────────────────────
   DEBUG LOG
───────────────────────────────────────────── */
error_log("CART: " . json_encode($_SESSION['cart']));

/* ─────────────────────────────────────────────
   RESPONSE
───────────────────────────────────────────── */
echo json_encode([
    'success' => true,
    'cart'    => $_SESSION['cart']
]);
?>