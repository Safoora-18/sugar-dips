<?php
session_start();
if (!isset($_SESSION['admin'])) { 
    header('Location: login.php'); 
    exit; 
}

require '../db.php';

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");

// Pre-fetch all items for all orders
$allItems = [];
$itemsResult = $conn->query("SELECT * FROM order_items");

while ($item = $itemsResult->fetch_assoc()) {
    $allItems[$item['order_id']][] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Orders — Admin</title>

  <link rel="stylesheet" href="../style.css"/>

  <style>

    body {
      background: #f8f9fa;
      font-family: var(--font-body);
    }

    .admin-layout {
      display: grid;
      grid-template-columns: 240px 1fr;
      min-height: 100vh;
    }

    .sidebar {
      background: var(--text);
      padding: 2rem 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .sidebar-logo {
      text-align: center;
      margin-bottom: 2rem;
    }

    .sidebar-logo img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
    }

    .sidebar-logo p {
      color: rgba(255,255,255,0.7);
      font-size: 0.8rem;
      margin-top: 0.5rem;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      color: rgba(255,255,255,0.7);
      text-decoration: none;
      padding: 0.75rem 1rem;
      border-radius: 10px;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.2s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: var(--pink);
      color: white;
    }

    .main {
      padding: 2rem;
    }

    .main-header {
      margin-bottom: 2rem;
    }

    .main-header h1 {
      font-family: var(--font-display);
      font-size: 1.8rem;
      color: var(--text);
    }

    .card {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .card-title {
      font-family: var(--font-display);
      font-size: 1.1rem;
      color: var(--text);
      margin-bottom: 1.25rem;
      font-style: italic;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--muted);
      padding: 0.5rem 0.75rem;
      border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }

    td {
      padding: 0.85rem 0.75rem;
      border-bottom: 1px solid var(--border);
      font-size: 0.875rem;
      vertical-align: middle;
    }

    tr:last-child td {
      border-bottom: none;
    }

    tr:hover td {
      background: var(--pink-soft);
    }

    .status-badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 50px;
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .status-pending {
      background: #fef3c7;
      color: #d97706;
    }

    .status-delivered {
      background: #dbeafe;
      color: #2563eb;
    }

    .status-cancelled {
      background: #fee2e2;
      color: #dc2626;
    }

    select.status-select {
      padding: 4px 8px;
      border-radius: 6px;
      border: 1px solid var(--border);
      font-size: 0.78rem;
      font-family: var(--font-body);
      cursor: pointer;
    }

    .view-btn {
      background: var(--pink);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 5px 12px;
      font-size: 0.78rem;
      cursor: pointer;
      font-weight: 600;
      white-space: nowrap;
    }

    .view-btn:hover {
      background: var(--pink-dark);
    }

    /* Modal */

    .modal-bg {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.55);
      z-index: 999;
      align-items: center;
      justify-content: center;
    }

    .modal-bg.open {
      display: flex;
    }

    .modal-box {
      background: white;
      border-radius: 20px;
      padding: 2rem;
      max-width: 520px;
      width: 90%;
      max-height: 85vh;
      overflow-y: auto;
    }

    .modal-box h2 {
      font-family: var(--font-display);
      margin-bottom: 0.25rem;
      font-size: 1.2rem;
      color: var(--text);
    }

    .modal-meta {
      font-size: 0.78rem;
      color: var(--muted);
      margin-bottom: 1.25rem;
    }

    .modal-item {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--border);
      gap: 1rem;
    }

    .modal-item:last-of-type {
      border-bottom: none;
    }

    .modal-item-name {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--text);
    }

    .modal-item-custom {
      font-size: 0.72rem;
      color: var(--muted);
      margin-top: 2px;
    }

    .modal-item-qty {
      font-size: 0.78rem;
      color: var(--muted);
    }

    .modal-item-price {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--pink);
      white-space: nowrap;
    }

    .modal-total {
      display: flex;
      justify-content: space-between;
      padding: 1rem 0 0;
      margin-top: 0.5rem;
      border-top: 2px solid var(--border);
    }

    .modal-total span:first-child {
      font-weight: 600;
      color: var(--text);
    }

    .modal-total span:last-child {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--pink);
      font-family: var(--font-display);
    }

    .modal-delivery {
      background: var(--pink-soft);
      border-radius: 10px;
      padding: 0.75rem 1rem;
      margin-bottom: 1rem;
      font-size: 0.82rem;
      color: var(--text);
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
    }

    .close-btn {
      margin-top: 1rem;
      width: 100%;
      text-align: center;
    }

    .no-orders {
      text-align: center;
      padding: 3rem;
      color: var(--muted);
      font-size: 0.95rem;
    }

  </style>
</head>

<body>

<div class="admin-layout">

  <div class="sidebar">

    <div class="sidebar-logo">
      <img src="../assets/Logo.png" alt="Sugar Dips"/>
      <p>Admin Panel</p>
    </div>

    <a href="index.php">📊 Dashboard</a>
    <a href="orders.php" class="active">🧾 Orders</a>
    <a href="products.php">🍫 Products</a>
    <a href="logout.php">🚪 Logout</a>

  </div>

  <div class="main">

    <div class="main-header">
      <h1>All Orders</h1>
    </div>

    <div class="card">

      <div class="card-title">
        Order Management
      </div>

      <?php if ($orders->num_rows === 0): ?>

        <div class="no-orders">
          No orders yet 🛍️
        </div>

      <?php else: ?>

      <table>

        <thead>
          <tr>
            <th>#ID</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Delivery Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Update</th>
            <th>Items</th>
          </tr>
        </thead>

        <tbody>

          <?php while ($o = $orders->fetch_assoc()):

            $items = $allItems[$o['id']] ?? [];

          ?>

          <tr>

            <td>
              <strong>#<?= $o['id'] ?></strong>
            </td>

            <td>
              <?= htmlspecialchars($o['customer_name']) ?>
            </td>

            <td>
              <?= htmlspecialchars($o['phone']) ?>
            </td>

            <td>
              <?= $o['delivery_date'] ?>
            </td>

            <td>
              <strong style="color:var(--pink)">
                ₹<?= $o['total'] ?>
              </strong>
            </td>

            <td>
              <span class="status-badge status-<?= $o['status'] ?>">
                <?= $o['status'] ?>
              </span>
            </td>

            <td>

              <select class="status-select"
                onchange="updateStatus(<?= $o['id'] ?>, this.value)">

                <option value="pending"
                  <?= $o['status']==='pending' ? 'selected' : '' ?>>
                  Pending
                </option>

                <option value="delivered"
                  <?= $o['status']==='delivered' ? 'selected' : '' ?>>
                  Delivered
                </option>

                <option value="cancelled"
                  <?= $o['status']==='cancelled' ? 'selected' : '' ?>>
                  Cancelled
                </option>

              </select>

            </td>

            <td>

              <button class="view-btn"

                onclick='showOrder(
                  <?= $o["id"] ?>,
                  "<?= addslashes($o['customer_name']) ?>",
                  "<?= addslashes($o['phone']) ?>",
                  "<?= addslashes($o['address']) ?>",
                  "<?= $o['delivery_date'] ?>",
                  "<?= addslashes($o['notes'] ?? '') ?>",
                  <?= $o['total'] ?>,
                  <?= json_encode($items) ?>
                )'>

                🧾 View

              </button>

            </td>

          </tr>

          <?php endwhile; ?>

        </tbody>

      </table>

      <?php endif; ?>

    </div>

  </div>

</div>

<!-- ORDER DETAIL MODAL -->

<div class="modal-bg" id="orderModal">

  <div class="modal-box">

    <h2 id="modal-title">Order #</h2>

    <div class="modal-meta" id="modal-meta"></div>

    <div class="modal-delivery" id="modal-delivery"></div>

    <div id="modal-items"></div>

    <div class="modal-total" id="modal-total"></div>

    <button class="btn-pink close-btn"
      onclick="document.getElementById('orderModal').classList.remove('open')">

      Close

    </button>

  </div>

</div>

<script>

function showOrder(id, name, phone, address, date, notes, total, items) {

  document.getElementById('modal-title').textContent =
    'Order #' + id + ' — ' + name;

  document.getElementById('modal-meta').textContent =
    '📞 ' + phone;

  let delivery =
    '📍 ' + address + '<br>📅 Delivery: ' + date;

  if (notes)
    delivery += '<br>📝 ' + notes;

  document.getElementById('modal-delivery').innerHTML = delivery;

  let html = '';

  if (items.length === 0) {

    html =
      '<div style="color:var(--muted);text-align:center;padding:1rem">No item details found</div>';

  } else {

    items.forEach(function(item) {

      html += `
      <div class="modal-item">

        <div>

          <div class="modal-item-name">
            ${item.product_name}
          </div>

          ${item.customization
            ? '<div class="modal-item-custom">📝 ' + item.customization + '</div>'
            : ''}

          <div class="modal-item-qty">
            Qty: ${item.qty}
          </div>

        </div>

        <div class="modal-item-price">
          ₹${item.price * item.qty}
        </div>

      </div>`;

    });

  }

  document.getElementById('modal-items').innerHTML = html;

  document.getElementById('modal-total').innerHTML =
    '<span>Total Amount</span><span>₹' + total + '</span>';

  document.getElementById('orderModal').classList.add('open');
}

function updateStatus(id, status) {

  fetch('update_status.php', {

    method: 'POST',

    headers: {
      'Content-Type': 'application/json'
    },

    body: JSON.stringify({
      id: id,
      status: status
    })

  })

  .then(function(r) {
    return r.json();
  })

  .then(function(d) {

    if (d.success) {

      location.reload();

    } else {

      alert('Error: ' + (d.message || 'Could not update status'));

    }

  })

  .catch(function(e) {

    alert('Network error: ' + e.message);

  });

}

// Close modal on background click

document.getElementById('orderModal')
.addEventListener('click', function(e) {

  if (e.target === this)
    this.classList.remove('open');

});

</script>

</body>
</html>