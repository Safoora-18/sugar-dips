<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
require 'db.php';
$userId = $_SESSION['user_id'];
$user   = $_SESSION['user_name'] ?? '';
$cart   = $_SESSION['cart'] ?? [];
$count  = array_sum(array_column($cart, 'qty'));
// Fetch all orders for this user with their items
$stmt = $conn->prepare(
    "SELECT o.*,
            GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR '||') AS item_names,
            GROUP_CONCAT(oi.qty          ORDER BY oi.id SEPARATOR '||') AS item_qtys,
            GROUP_CONCAT(oi.price        ORDER BY oi.id SEPARATOR '||') AS item_prices
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     WHERE o.user_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders — Sugar Dips</title>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .track-wrap {
      max-width: 720px;
      margin: 2rem auto;
      padding: 0 1rem 4rem;
    }

    .track-hero {
      margin-bottom: 2rem;
    }

    .track-hero h1 {
      font-family: var(--font-display);
      font-size: 1.9rem;
      color: var(--text);
    }

    .track-hero p {
      color: var(--muted);
      font-size: 0.9rem;
      margin-top: 0.25rem;
    }

    /* Order card */
    .order-card {
      background: white;
      border-radius: 18px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.07);
      border: 1.5px solid var(--border);
      margin-bottom: 1.5rem;
      overflow: hidden;
    }

    .order-card-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.25rem;
      border-bottom: 1px solid var(--border);
      background: var(--pink-soft);
    }

    .order-id {
      font-weight: 700;
      color: var(--pink);
      font-size: 0.95rem;
    }

    .order-date {
      font-size: 0.78rem;
      color: var(--muted);
    }

    .order-body {
      padding: 1.25rem;
    }

    /* Items list */
    .order-items {
      margin-bottom: 1rem;
    }

    .order-item-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.875rem;
      color: var(--text);
      padding: 0.3rem 0;
      border-bottom: 1px dashed var(--border);
    }

    .order-item-row:last-child {
      border: none;
    }

    /* Meta row */
    .order-meta {
      display: flex;
      gap: 1.5rem;
      flex-wrap: wrap;
      font-size: 0.82rem;
      color: var(--muted);
      margin-bottom: 1rem;
    }

    .order-meta span b {
      color: var(--text);
    }

    .order-total-row {
      display: flex;
      justify-content: space-between;
      font-size: 1rem;
      font-weight: 700;
      color: var(--text);
      padding-top: 0.75rem;
      border-top: 2px solid var(--border);
    }

    .order-total-row span:last-child {
      color: var(--pink);
      font-family: var(--font-display);
      font-size: 1.15rem;
    }

    /* Discount badge */
    .discount-badge {
      display: inline-block;
      background: #d1fae5;
      color: #065f46;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 20px;
      margin-left: 0.5rem;
    }

    /* Status badge */
    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 50px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
    }

    .status-pending {
      background: #fef3c7;
      color: #d97706;
    }

    .status-confirmed {
      background: #d1fae5;
      color: #059669;
    }

    .status-delivered {
      background: #dbeafe;
      color: #2563eb;
    }

    .status-cancelled {
      background: #fee2e2;
      color: #dc2626;
    }

    /* Payment badge */
    .pay-paid {
      background: #d1fae5;
      color: #059669;
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 0.72rem;
      font-weight: 700;
    }

    .pay-pending {
      background: #fef3c7;
      color: #d97706;
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 0.72rem;
      font-weight: 700;
    }

    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 5rem 1rem;
      color: var(--muted);
    }

    .empty-state div {
      font-size: 5rem;
      margin-bottom: 1rem;
    }

    .empty-state p {
      font-size: 1rem;
      margin-bottom: 1.5rem;
    }

    /* Filter tabs */
    .filter-tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }

    .tab-btn {
      padding: 0.4rem 1rem;
      border-radius: 50px;
      border: 1.5px solid var(--border);
      background: white;
      font-size: 0.82rem;
      cursor: pointer;
      font-family: var(--font-body);
      color: var(--muted);
      transition: all 0.2s;
    }

    .tab-btn.active {
      background: var(--pink);
      border-color: var(--pink);
      color: white;
      font-weight: 600;
    }

    @media (max-width: 600px) {
      .order-card-top {
        flex-direction: column;
        gap: 0.4rem;
        align-items: flex-start;
      }

      .order-meta {
        gap: 0.75rem;
      }
    }
  </style>
</head>

<body>

<nav>
  <a href="index.php" class="nav-logo">
    <img src="assets/Logo.png" alt="Sugar Dips"/>
  </a>

  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="menu.php">Menu</a></li>
    <li><a href="track.php" style="color:var(--pink);font-weight:600">My Orders</a></li>
  </ul>

  <div class="nav-actions">
    <button onclick="openCart()" class="cart-btn">
      🛒
      <?php if ($count > 0) echo "<span class='cart-badge'>$count</span>"; ?>
    </button>

    <span class="user-name">
      👤 <?= htmlspecialchars(explode(' ', $user)[0]) ?>
    </span>

    <a href="logout.php" class="btn-outline"
       style="font-size:0.78rem;padding:0.35rem 0.9rem;">
      Logout
    </a>
  </div>
</nav>

<?php include 'cart_sidebar.php'; ?>

<div class="track-wrap">

  <div class="track-hero">
    <h1>📦 My Orders</h1>
    <p><?= count($orders) ?> order<?= count($orders) != 1 ? 's' : '' ?> placed</p>
  </div>

  <?php if (empty($orders)): ?>

    <div class="empty-state">
      <div>🛍️</div>
      <p>No orders yet! Start exploring our menu.</p>
      <a href="menu.php" class="btn-pink">Browse Menu 🍫</a>
    </div>

  <?php else: ?>

    <!-- Filter tabs -->
    <div class="filter-tabs">
      <button class="tab-btn active" onclick="filterOrders('all', this)">All</button>
      <button class="tab-btn" onclick="filterOrders('pending', this)">Pending</button>
      <button class="tab-btn" onclick="filterOrders('confirmed', this)">Confirmed</button>
      <button class="tab-btn" onclick="filterOrders('delivered', this)">Delivered</button>
      <button class="tab-btn" onclick="filterOrders('cancelled', this)">Cancelled</button>
    </div>

    <?php foreach ($orders as $o):

      $names  = $o['item_names']  ? explode('||', $o['item_names'])  : [];
      $qtys   = $o['item_qtys']   ? explode('||', $o['item_qtys'])   : [];
      $prices = $o['item_prices'] ? explode('||', $o['item_prices']) : [];

      $formattedDate = date(' d-m-y', strtotime($o['created_at']));
    ?>

    <div class="order-card" data-status="<?= $o['status'] ?>">

      <!-- Card Header -->
      <div class="order-card-top">

        <div>
          <div class="order-id">
            Order #<?= $o['id'] ?>
          </div>

          <div class="order-date">
            Placed on <?= $formattedDate ?>
          </div>
        </div>

        <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap">

          <span class="status-badge status-<?= $o['status'] ?>">
            <?= $o['status'] ?>
          </span>

          <?php if (!empty($o['payment_status'])): ?>

            <span class="pay-<?= $o['payment_status'] === 'paid' ? 'paid' : 'pending' ?>">

              <?= $o['payment_status'] === 'paid'
                  ? '✅ Paid'
                  : '⏳ COD' ?>

            </span>

          <?php endif; ?>

        </div>
      </div>

      <!-- Card Body -->
      <div class="order-body">

        <!-- Meta info -->
        <div class="order-meta">

          <span>
            📅 <b>Delivery:</b>
            <?= htmlspecialchars($o['delivery_date']) ?>
          </span>

          <?php if (!empty($o['time_slot'])): ?>

            <span>
              🕐 <b>Slot:</b>
              <?= htmlspecialchars($o['time_slot']) ?>
            </span>

          <?php endif; ?>

          <span>
            📍 <b>Address:</b>
            <?= htmlspecialchars(substr($o['address'], 0, 40)) ?>...
          </span>

        </div>

        <!-- Items -->
        <?php if (!empty($names)): ?>

        <div class="order-items">

          <?php foreach ($names as $i => $name): ?>

          <div class="order-item-row">

            <span>
              🍫 <?= htmlspecialchars($name) ?> ×<?= $qtys[$i] ?>
            </span>

            <span>
              ₹<?= (int)$prices[$i] * (int)$qtys[$i] ?>
            </span>

          </div>

          <?php endforeach; ?>

        </div>

        <?php endif; ?>

        <!-- Total -->
        <div class="order-total-row">

          <span>
            Total

            <?php if (!empty($o['discount']) && $o['discount'] > 0): ?>

              <span class="discount-badge">
                -₹<?= $o['discount'] ?> off
              </span>

            <?php endif; ?>

            <?php if (!empty($o['coupon_code'])): ?>

              <span class="discount-badge">
                🎟 <?= htmlspecialchars($o['coupon_code']) ?>
              </span>

            <?php endif; ?>

          </span>

          <span>
            ₹<?= $o['total'] ?>
          </span>

        </div>

        <!-- Cancelled Message -->
        <?php if ($o['status'] === 'cancelled'): ?>

        <div style="
          padding:0.75rem;
          background:#fee2e2;
          border-radius:10px;
          text-align:center;
          font-size:0.875rem;
          color:#dc2626;
          font-weight:600;
        ">
          ❌ This order was cancelled
        </div>

        <?php endif; ?>

      </div>
    </div>

    <?php endforeach; ?>

  <?php endif; ?>

</div>

<script>
function filterOrders(status, btn) {

  document.querySelectorAll('.tab-btn').forEach(b => {
    b.classList.remove('active');
  });

  btn.classList.add('active');

  document.querySelectorAll('.order-card').forEach(card => {

    if (status === 'all' || card.dataset.status === status) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }

  });
}
</script>

</body>
</html>