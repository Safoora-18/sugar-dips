<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
if (!isset($_SESSION['order_data'])) {
    header('Location: index.php'); exit;
}

$data       = $_SESSION['order_data'];
$user       = $_SESSION['user_name'] ?? '';
$orderId    = $data['orderId'];
$name       = $data['name'];
$phone      = $data['phone'];
$address    = $data['address'];
$date       = $data['date'];
$notes      = $data['notes'] ?? '';
$items      = $data['items'];
$orderTotal = $data['total'];  // renamed from $total to avoid being overwritten by cart_sidebar.php

// Recalculate from items to be safe
if (!$orderTotal || $orderTotal == 0) {
    $orderTotal = 0;
    foreach ($items as $i) {
        $orderTotal += (int)($i['price'] ?? 0) * (int)($i['qty'] ?? 1);
    }
}

unset($_SESSION['order_data']);

// Build WhatsApp message
$itemLines = '';
foreach ($items as $i) {
    $lineTotal  = (int)($i['price'] ?? 0) * (int)($i['qty'] ?? 1);
    $itemLines .= "%0A• " . rawurlencode($i['name']) . " x" . $i['qty'] . " = Rs." . $lineTotal;
}

$waMsg = rawurlencode("*New Order - Sugar Dips*") .
    "%0A%0A" .
    rawurlencode("Name: " . $name) .
    "%0A" .
    rawurlencode("Phone: " . $phone) .
    "%0A" .
    rawurlencode("Address: " . $address) .
    "%0A" .
    rawurlencode("Delivery: " . $date) .
    ($notes ? "%0A" . rawurlencode("Notes: " . $notes) : "") .
    "%0A%0A" .
    rawurlencode("*Items Ordered:*") .
    $itemLines .
    "%0A%0A" .
    rawurlencode("*Total: Rs." . $orderTotal . "*") .
    "%0A" .
    rawurlencode("Order ID: #" . $orderId);

$waLink = "https://wa.me/919739956250?text=" . $waMsg;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Confirmed — Sugar Dips</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo">
    <img src="assets/Logo.png" alt="Sugar Dips"/>
  </a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="menu.php">Menu</a></li>
  </ul>
  <div class="nav-actions">
    <button onclick="openCart()" class="cart-btn">🛒</button>
    <span class="user-name">👤 <?= htmlspecialchars(explode(' ', $user)[0]) ?></span>
    <a href="logout.php" class="btn-outline"
       style="font-size:0.78rem;padding:0.35rem 0.9rem;">Logout</a>
  </div>
</nav>

<?php include 'cart_sidebar.php'; ?>

<div class="confirm-page">
  <div class="confirm-card">

    <div class="confirm-icon">🎉</div>
    <h1 class="confirm-title">Order Placed!</h1>
    <p class="confirm-sub">
      Thank you, <strong><?= htmlspecialchars($name) ?></strong>!
      Your order has been confirmed.
    </p>

    <div class="order-id-badge">
      Order ID: <strong>#<?= $orderId ?></strong>
    </div>

    <!-- Delivery Summary -->
    <div class="confirm-summary">
      <div class="confirm-row">
        <span>📍</span>
        <span><?= htmlspecialchars($address) ?></span>
      </div>
      <div class="confirm-row">
        <span>📅</span>
        <span>Delivery on <?= htmlspecialchars($date) ?></span>
      </div>
      <?php if ($notes): ?>
      <div class="confirm-row">
        <span>📝</span>
        <span><?= htmlspecialchars($notes) ?></span>
      </div>
      <?php endif; ?>
      <div class="confirm-row">
        <span>💰</span>
        <span style="font-weight:700;color:var(--pink)">Total: ₹<?= $orderTotal ?></span>
      </div>
    </div>

    <!-- Items -->
    <div class="confirm-items">
      <?php foreach ($items as $item):
        $lineTotal = (int)($item['price'] ?? 0) * (int)($item['qty'] ?? 1);
      ?>
      <div class="confirm-item">
        <img src="<?= htmlspecialchars($item['image'] ?? '') ?>"
             alt="<?= htmlspecialchars($item['name']) ?>"
             onerror="this.style.display='none'"/>
        <div class="confirm-item-name"><?= htmlspecialchars($item['name']) ?></div>
        <div class="confirm-item-qty">×<?= $item['qty'] ?></div>
        <div class="confirm-item-price">₹<?= $lineTotal ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Total -->
    <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-top:2px solid var(--border);margin-bottom:1.25rem">
      <span style="font-weight:600">Total Amount</span>
      <span style="font-size:1.4rem;font-weight:700;color:var(--pink);font-family:var(--font-display)">₹<?= $orderTotal ?></span>
    </div>

    <!-- WhatsApp Button -->
    <a href="<?= htmlspecialchars($waLink) ?>"
       target="_blank" class="wa-btn">
      💬 Send via WhatsApp
    </a>
    <p class="wa-note">
      Tap above to send your order details to Sugar Dips on WhatsApp
    </p>

    <div class="confirm-actions">
      <a href="menu.php" class="btn-pink">Order More 🍫</a>
      <a href="index.php" class="btn-outline">Back to Home</a>
    </div>

  </div>
</div>

</body>
</html>