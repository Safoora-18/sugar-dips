<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
require 'db.php';
$conn->set_charset('utf8mb4');

$userId = $_SESSION['user_id'];
$user   = $_SESSION['user_name'] ?? '';
$cart   = $_SESSION['cart'] ?? [];
$count  = array_sum(array_column($cart, 'qty'));

// Get user's orders
$orders = $conn->prepare(
    "SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC"
);
$orders->bind_param("i", $userId);
$orders->execute();
$ordersResult = $orders->get_result();

// Get all items for these orders
$allItems = [];
$items = $conn->query("SELECT * FROM order_items");
while ($item = $items->fetch_assoc()) {
    $allItems[$item['order_id']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders — Sugar Dips</title>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .orders-page { padding: 5rem 4rem 4rem; max-width: 900px; margin: 0 auto; }
    .orders-title { font-family: var(--font-display); font-size: 2rem; color: var(--text); margin-bottom: 0.5rem; }
    .orders-sub { font-size: 0.9rem; color: var(--muted); margin-bottom: 2.5rem; }
    .order-card {
      background: white; border-radius: 20px; padding: 1.5rem;
      box-shadow: 0 2px 16px rgba(232,66,122,0.08);
      border: 1px solid var(--border); margin-bottom: 1.25rem;
    }
    .order-card-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;
    }
    .order-id { font-family: var(--font-display); font-size: 1rem; color: var(--text); font-style: italic; }
    .order-date { font-size: 0.78rem; color: var(--muted); }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; }
    .status-pending   { background: #fef3c7; color: #d97706; }
    .status-confirmed { background: #d1fae5; color: #059669; }
    .status-delivered { background: #dbeafe; color: #2563eb; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }
    .order-items { display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1rem; }
    .order-item { display: flex; align-items: center; gap: 0.75rem; }
    .order-item img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
    .order-item-name { flex: 1; font-size: 0.82rem; font-weight: 500; color: var(--text); }
    .order-item-qty { font-size: 0.75rem; color: var(--muted); }
    .order-item-price { font-size: 0.875rem; font-weight: 700; color: var(--pink); }
    .order-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 0.5rem; }
    .order-total { font-family: var(--font-display); font-size: 1.2rem; color: var(--pink); font-weight: 600; }
    .order-delivery { font-size: 0.78rem; color: var(--muted); }
    .reorder-btn { background: var(--pink-soft); color: var(--pink); border: 1px solid var(--pink-light); border-radius: 50px; padding: 0.4rem 1rem; font-size: 0.78rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; }
    .reorder-btn:hover { background: var(--pink); color: white; }
    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
    .empty-state h2 { font-family: var(--font-display); font-size: 1.5rem; margin-bottom: 0.5rem; }
    .empty-state p { color: var(--muted); margin-bottom: 1.5rem; }
    @media (max-width: 768px) { .orders-page { padding: 5rem 1.5rem 3rem; } }
  </style>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo"><img src="assets/Logo.png" alt="Sugar Dips"/></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="menu.php">Menu</a></li>
    <li><a href="my_orders.php">My Orders</a></li>
  </ul>
  <div class="nav-actions">
    <button onclick="openCart()" class="cart-btn">
      🛒
      <?php if ($count > 0) echo "<span class='cart-badge'>$count</span>"; ?>
    </button>
    <span class="user-name">👤 <?= htmlspecialchars(explode(' ',$user)[0]) ?></span>
    <a href="logout.php" class="btn-outline"
       style="font-size:0.78rem;padding:0.35rem 0.9rem;">Logout</a>
  </div>
</nav>

<?php include 'cart_sidebar.php'; ?>

<div class="orders-page">
  <h1 class="orders-title">📦 My Orders</h1>
  <p class="orders-sub">Track all your Sugar Dips orders</p>

  <?php if ($ordersResult->num_rows === 0): ?>
    <div class="empty-state">
      <div class="empty-icon">🛍️</div>
      <h2>No orders yet!</h2>
      <p>You haven't placed any orders yet. Start browsing our menu!</p>
      <a href="menu.php" class="btn-pink">Browse Menu 🍫</a>
    </div>

  <?php else: ?>
    <?php while ($order = $ordersResult->fetch_assoc()):
      $orderItems = $allItems[$order['id']] ?? [];
    ?>
    <div class="order-card">
      <div class="order-card-header">
        <div>
          <div class="order-id">Order #<?= $order['id'] ?></div>
          <div class="order-date">
            Placed on <?= date('d M Y', strtotime($order['created_at'])) ?>
          </div>
        </div>
        <span class="status-badge status-<?= $order['status'] ?>">
          <?= ucfirst($order['status']) ?>
        </span>
      </div>

      <!-- Items -->
      <div class="order-items">
        <?php foreach ($orderItems as $item): ?>
        <div class="order-item">
          <div style="flex:1">
            <div class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
            <?php if ($item['customization']): ?>
              <div style="font-size:0.7rem;color:var(--muted)">
                📝 <?= htmlspecialchars($item['customization']) ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="order-item-qty">×<?= $item['qty'] ?></div>
          <div class="order-item-price">₹<?= $item['price'] * $item['qty'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="order-footer">
        <div>
          <div class="order-total">₹<?= $order['total'] ?></div>
          <div class="order-delivery">
            📅 Delivery: <?= date('d M Y', strtotime($order['delivery_date'])) ?>
          </div>
          <div class="order-delivery">
            📍 <?= htmlspecialchars(substr($order['address'], 0, 50)) ?>...
          </div>
        </div>
        <a href="menu.php" class="reorder-btn">🔄 Order Again</a>
      </div>
    </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

</body>
</html>