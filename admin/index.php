<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../db.php';

$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];

$totalSales = $conn->query("SELECT SUM(total) FROM orders")->fetch_row()[0];

$pendingOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetch_row()[0];

$mostOrdered = $conn->query("SELECT product_name, SUM(qty) as total FROM order_items GROUP BY product_name ORDER BY total DESC LIMIT 1")->fetch_assoc();

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — Admin</title>
  <link rel="stylesheet" href="../style.css"/>
  <style>
    body { background: #f8f9fa; font-family: var(--font-body); }
    .admin-layout { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
    .sidebar { background: var(--text); padding: 2rem 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
    .sidebar-logo { text-align: center; margin-bottom: 2rem; }
    .sidebar-logo img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
    .sidebar-logo p { color: rgba(255,255,255,0.7); font-size: 0.8rem; margin-top: 0.5rem; }
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; color: rgba(255,255,255,0.7); text-decoration: none; padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.875rem; font-weight: 500; transition: all 0.2s; }
    .sidebar a:hover, .sidebar a.active { background: var(--pink); color: white; }
    .main { padding: 2rem; }
    .main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .main-header h1 { font-family: var(--font-display); font-size: 1.8rem; color: var(--text); }
    .main-header span { font-size: 0.85rem; color: var(--muted); }
    .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.25rem; margin-bottom: 2rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-left: 4px solid var(--pink); }
    .stat-card.teal  { border-left-color: var(--teal); }
    .stat-card.green { border-left-color: #22c55e; }
    .stat-card.orange{ border-left-color: #f59e0b; }
    .stat-label { font-size: 0.78rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem; }
    .stat-value { font-family: var(--font-display); font-size: 2rem; color: var(--text); font-weight: 600; }
    .stat-sub { font-size: 0.75rem; color: var(--muted); margin-top: 0.25rem; }
    .card { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 1.5rem; }
    .card-title { font-family: var(--font-display); font-size: 1.1rem; color: var(--text); margin-bottom: 1.25rem; font-style: italic; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--border); }
    td { padding: 0.85rem 0.75rem; border-bottom: 1px solid var(--border); font-size: 0.875rem; color: var(--text); }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--pink-soft); }
    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    .status-pending   { background: #fef3c7; color: #d97706; }
    .status-confirmed { background: #d1fae5; color: #059669; }
    .status-delivered { background: #dbeafe; color: #2563eb; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }
    select.status-select { padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.78rem; font-family: var(--font-body); cursor: pointer; }
  </style>
</head>
<body>
<div class="admin-layout">
  <div class="sidebar">
    <div class="sidebar-logo">
      <img src="../assets/Logo.png" alt="Sugar Dips"/>
      <p>Admin Panel</p>
    </div>
    <a href="index.php" class="active">📊 Dashboard</a>
    <a href="orders.php">🧾 Orders</a>
    <a href="products.php">🍫 Products</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <div class="main">
    <div class="main-header">
      <h1>Dashboard</h1>
      <span>Welcome back, Admin 👋</span>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value"><?= $totalOrders ?></div>
        <div class="stat-sub">All time</div>
      </div>
      <div class="stat-card teal">
        <div class="stat-label">Total Sales</div>
        <div class="stat-value">₹<?= number_format($totalSales) ?></div>
        <div class="stat-sub">Revenue</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-label">Pending Orders</div>
        <div class="stat-value"><?= $pendingOrders ?></div>
        <div class="stat-sub">Need attention</div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Most Ordered</div>
        <div class="stat-value" style="font-size:0.95rem;line-height:1.3">
          <?= $mostOrdered ? htmlspecialchars(substr($mostOrdered['product_name'], 0, 20)) : 'N/A' ?>
        </div>
        <div class="stat-sub"><?= $mostOrdered ? $mostOrdered['total'] . ' orders' : '' ?></div>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Recent Orders</div>
      <table>
        <thead>
          <tr>
            <th>#ID</th><th>Customer</th><th>Phone</th><th>Total</th><th>Delivery Date</th><th>Status</th><th>Update</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($order = $orders->fetch_assoc()): ?>
          <tr>
            <td><strong>#<?= $order['id'] ?></strong></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td><?= htmlspecialchars($order['phone']) ?></td>
            <td><strong style="color:var(--pink)">₹<?= $order['total'] ?></strong></td>
            <td><?= $order['delivery_date'] ?></td>
            <td><span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span></td>
            <td>
              <select class="status-select" onchange="updateStatus(<?= $order['id'] ?>, this.value)">
                <option value="pending"   <?= $order['status']==='pending'   ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $order['status']==='confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="delivered" <?= $order['status']==='delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $order['status']==='cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function updateStatus(id, status) {
  fetch('update_status.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id, status})
  }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>
</body>
</html>