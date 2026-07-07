<?php
session_start();
$cart  = $_SESSION['cart'] ?? [];
$count = array_sum(array_column($cart, 'qty'));
$total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));
?>

<!-- Badge count for JS to read -->
<?php if ($count > 0): ?>
<span class="cart-badge-count" style="display:none"><?= $count ?></span>
<?php endif; ?>

<?php if (empty($cart)): ?>
  <div class="cart-sidebar-empty">
    <div style="font-size:3.5rem">🛍️</div>
    <p>Your cart is empty!</p>
    <a href="menu.php" class="btn-pink">Browse Menu</a>
  </div>
<?php else: ?>
  <div class="cart-sidebar-items">
    <?php foreach ($cart as $key => $item): ?>
    <div class="cart-sidebar-item">
      <img src="<?= htmlspecialchars($item['image']) ?>"
           alt="<?= htmlspecialchars($item['name']) ?>" />
      <div class="cart-sidebar-item-info">
        <div class="cart-sidebar-item-name"><?= htmlspecialchars($item['name']) ?></div>
        <div class="cart-sidebar-item-price">₹<?= $item['price'] ?> each</div>
        <div class="cart-sidebar-item-qty">
          <button class="qty-btn"
            onclick="updateCart('<?= $key ?>', <?= $item['qty']-1 ?>)">−</button>
          <span class="qty-num"><?= $item['qty'] ?></span>
          <button class="qty-btn"
            onclick="updateCart('<?= $key ?>', <?= $item['qty']+1 ?>)">+</button>
          <button onclick="removeCart('<?= $key ?>')"
            style="background:none;border:none;cursor:pointer;margin-left:0.4rem;font-size:0.9rem;opacity:0.6">🗑️</button>
        </div>
      </div>
      <div class="cart-sidebar-item-total">₹<?= $item['price'] * $item['qty'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="cart-sidebar-footer">
    <div class="cart-sidebar-total">
      <span>Total</span>
      <span class="cart-total-amt">₹<?= $total ?></span>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="order.php" class="btn-pink cart-checkout-btn">Proceed to Order →</a>
    <?php else: ?>
      <a href="login.php" class="btn-pink cart-checkout-btn">Login to Order →</a>
    <?php endif; ?>
    <button onclick="clearCart()" class="cart-clear-btn">Clear Cart</button>
  </div>
<?php endif; ?>