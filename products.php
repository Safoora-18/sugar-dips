<?php
require_once 'db.php';

$rows     = $conn->query("SELECT * FROM products_new ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$products = [];
foreach ($rows as $r) {
    $products[] = [
        'id'           => $r['id'],
        'name'         => $r['name'],
        'desc'         => $r['description'],
        'price'        => $r['price'],
        'image'        => $r['image'],
        'category'     => $r['category'],
        'customizable' => (bool)$r['customizable'],
        'type'         => $r['type'] ?? null,
    ];
}

$flavours = ['Plain','Nutella','Ferrero','Twix','Salted Caramel','Oreo','Pistachio'];
?>