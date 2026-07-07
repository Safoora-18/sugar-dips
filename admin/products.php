<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../db.php';

$msg   = $_SESSION['msg']   ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['msg'], $_SESSION['error']);

$products = $conn->query("SELECT * FROM products_new ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Products — Admin</title>
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
    .card { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 1.5rem; }
    .card-title { font-family: var(--font-display); font-size: 1.1rem; color: var(--text); margin-bottom: 1.25rem; font-style: italic; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group { display: flex; flex-direction: column; gap: 0.4rem; }
    .form-group label { font-size: 0.78rem; font-weight: 600; color: var(--text); text-transform: uppercase; letter-spacing: 0.04em; }
    .form-group input, .form-group select, .form-group textarea { padding: 0.75rem 1rem; border: 1.5px solid var(--border); border-radius: 10px; font-size: 0.9rem; font-family: var(--font-body); outline: none; }
    .form-group input:focus, .form-group select:focus { border-color: var(--pink); }
    .form-group input[type="file"] { padding: 0.5rem; cursor: pointer; }
    .img-preview { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; display: none; margin-top: 0.5rem; border: 2px dashed var(--border); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--border); }
    td { padding: 0.85rem 0.75rem; border-bottom: 1px solid var(--border); font-size: 0.875rem; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--pink-soft); }
    td img { width: 56px; height: 56px; border-radius: 10px; object-fit: cover; }
    .badge { font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 20px; background: var(--pink-soft); color: var(--pink); font-weight: 600; }
    .customizable-yes { color: #059669; font-weight: 600; font-size: 0.82rem; }
    .customizable-no  { color: var(--muted); font-size: 0.82rem; }
    .msg   { background: #d1fae5; color: #059669; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.875rem; }
    .err   { background: #fee2e2; color: #dc2626; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.875rem; }
    .btn-delete { background: none; border: 1.5px solid #fca5a5; color: #dc2626; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.78rem; cursor: pointer; transition: all 0.2s; }
    .btn-delete:hover { background: #dc2626; color: white; }
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
    <a href="orders.php">🧾 Orders</a>
    <a href="products.php" class="active">🍫 Products</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <div class="main">
    <div class="main-header">
      <h1>Products</h1>
      <button class="btn-pink" onclick="toggleForm()">+ Add Product</button>
    </div>

    <?php if ($msg):   ?><div class="msg">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="err">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- ADD FORM -->
    <div class="card" id="add-form" style="display:none">
      <div class="card-title">Add New Product</div>
      <form action="product_action.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add"/>
        <div class="form-grid">
          <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="name" placeholder="e.g. Nutella Brownie" required/>
          </div>
          <div class="form-group">
            <label>Price (₹) *</label>
            <input type="number" name="price" placeholder="150" step="0.01" min="0" required/>
          </div>
          <div class="form-group">
            <label>Category *</label>
            <select name="category">
              <option>Brownies</option>
              <option>Cheesecakes</option>
              <option>Specials</option>
              <option>Dates</option>
              <option>Cookies</option>
            </select>
          </div>
          <div class="form-group">
            <label>Customizable?</label>
            <select name="customizable">
              <option value="0">No</option>
              <option value="1">Yes</option>
            </select>
          </div>
          <div class="form-group" style="grid-column:1/-1">
            <label>Description</label>
            <input type="text" name="description" placeholder="Short product description"/>
          </div>
          <div class="form-group" style="grid-column:1/-1">
            <label>Product Image *</label>
            <input type="file" name="image" accept="image/*" required
                   onchange="previewImg(this)"/>
            <img id="img-preview" class="img-preview" alt="Preview"/>
          </div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:1.25rem">
          <button type="submit" class="btn-pink">💾 Save Product</button>
          <button type="button" class="btn-outline" onclick="toggleForm()">Cancel</button>
        </div>
      </form>
    </div>

    <!-- PRODUCTS TABLE -->
    <div class="card">
      <div class="card-title">All Products (<?= count($products) ?>)</div>
      <table>
        <thead>
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Category</th>
            <th>Description</th>
            <th>Customizable</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr>
            <td><img src="../<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"/></td>
            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
            <td style="color:var(--pink);font-weight:700">₹<?= number_format($p['price'], 0) ?></td>
            <td><span class="badge"><?= htmlspecialchars($p['category']) ?></span></td>
            <td style="max-width:240px;font-size:0.82rem;color:var(--muted);line-height:1.4"><?= htmlspecialchars($p['description']) ?></td>
            <td>
              <?php if ($p['customizable']): ?>
                <span class="customizable-yes">✅ Yes</span>
              <?php else: ?>
                <span class="customizable-no">—</span>
              <?php endif; ?>
            </td>
            <td>
              <form action="product_action.php" method="POST"
                    onsubmit="return confirm('Delete \'<?= htmlspecialchars($p['name']) ?>\'?')">
                <input type="hidden" name="action" value="delete"/>
                <input type="hidden" name="id"    value="<?= $p['id'] ?>"/>
                <input type="hidden" name="image" value="<?= htmlspecialchars($p['image']) ?>"/>
                <button type="submit" class="btn-delete">🗑 Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function toggleForm() {
  const f = document.getElementById('add-form');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
  window.scrollTo(0, 0);
}
function previewImg(input) {
  const preview = document.getElementById('img-preview');
  if (input.files && input.files[0]) {
    preview.src = URL.createObjectURL(input.files[0]);
    preview.style.display = 'block';
  }
}
</script>
</body>
</html>