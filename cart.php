<?php
session_start();
$user = $_SESSION['user_name'] ?? null;
$cart = $_SESSION['cart'] ?? [];
$count = array_sum(array_column($cart, 'qty'));
$total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cart — Sugar Dips</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo"><img src="assets/Logo.png" alt="Sugar Dips" /></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="menu.php">Menu</a></li>
    <li><a href="index.php#about">About</a></li>
  </ul>
  <div class="nav-actions">
    <a href="cart.php" class="cart-btn">
      🛒
      <?php if ($count > 0) echo "<span class='cart-badge'>$count</span>"; ?>
    </a>
    <?php if ($user): ?>
      <span class="user-name">👤 <?= htmlspecialchars(explode(' ', $user)[0]) ?></span>
      <a href="logout.php" class="btn-outline" style="font-size:0.78rem;padding:0.35rem 0.9rem;">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn-pink">Login</a>
    <?php endif; ?>
  </div>
</nav>

<div class="page cart-page">
  <h1 class="cart-title">🛒 Your Cart</h1>

  <?php if (empty($cart)): ?>
    <div class="cart-empty">
      <div style="font-size:4rem">🛍️</div>
      <p>Your cart is empty!</p>
      <a href="menu.php" class="btn-pink">Browse Menu</a>
    </div>
  <?php else: ?>
    <div class="cart-layout">
      <div class="cart-items">
        <?php foreach ($cart as $key => $item): ?>
        <div class="cart-item">
          <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
          <div class="cart-item-info">
            <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="cart-item-price">₹<?= $item['price'] ?> each</div>
            <div class="cart-item-qty">
              <button class="qty-btn" onclick="updateCart('<?= $key ?>', <?= $item['qty']-1 ?>)">−</button>
              <span class="qty-num"><?= $item['qty'] ?></span>
              <button class="qty-btn" onclick="updateCart('<?= $key ?>', <?= $item['qty']+1 ?>)">+</button>
              <button onclick="removeCart('<?= $key ?>')" style="background:none;border:none;cursor:pointer;margin-left:0.5rem;font-size:1rem">🗑️</button>
            </div>
          </div>
          <div class="cart-item-total">₹<?= $item['price'] * $item['qty'] ?></div>
        </div>
        <?php endforeach; ?>
        <button onclick="clearCart()" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:0.82rem;text-decoration:underline;margin-top:0.5rem">Clear Cart</button>
      </div>

      <div class="summary-card">
        <div class="summary-title">🧾 Order Summary</div>
        <?php foreach ($cart as $item): ?>
        <div class="summary-row">
          <span><?= htmlspecialchars($item['name']) ?> ×<?= $item['qty'] ?></span>
          <span>₹<?= $item['price'] * $item['qty'] ?></span>
        </div>
        <?php endforeach; ?>
        <div class="summary-total">
          <span>Total</span>
          <span>₹<?= $total ?></span>
        </div>
        <?php if ($user): ?>
          <a href="order.php" class="btn-pink checkout-btn">Proceed to Order →</a>
        <?php else: ?>
          <a href="login.php" class="btn-pink checkout-btn">Login to Order →</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
function updateCart(key, qty) {
  fetch('cart_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'update', key, qty})
  }).then(() => location.reload());
}
function removeCart(key) {
  fetch('cart_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'remove', key})
  }).then(() => location.reload());
}
function clearCart() {
  fetch('cart_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'clear'})
  }).then(() => location.reload());
}
</script>
</body>
</html>