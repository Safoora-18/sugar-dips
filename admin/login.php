<?php
session_start();
if (isset($_SESSION['admin'])) {
    header('Location: index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    // Default: admin / sugardips123
    if ($user === 'admin' && $pass === 'sugardips123') {
        $_SESSION['admin'] = true;
        header('Location: index.php'); exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login — Sugar Dips</title>
  <link rel="stylesheet" href="../style.css"/>
  <style>
    body {
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, var(--pink-soft), var(--teal-soft));
    }
    .login-card {
      background: white; border-radius: 24px;
      padding: 2.5rem 2rem; width: 100%; max-width: 380px;
      box-shadow: 0 20px 60px rgba(232,66,122,0.15);
      text-align: center;
    }
    .login-card img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem; }
    .login-card h1 { font-family: var(--font-display); font-size: 1.4rem; margin-bottom: 0.4rem; }
    .login-card p { font-size: 0.82rem; color: var(--muted); margin-bottom: 2rem; }
    .form-group { display: flex; flex-direction: column; gap: 0.4rem; text-align: left; margin-bottom: 1rem; }
    .form-group label { font-size: 0.78rem; font-weight: 600; color: var(--text); text-transform: uppercase; letter-spacing: 0.04em; }
    .form-group input { padding: 0.75rem 1rem; border: 1.5px solid var(--border); border-radius: 12px; font-size: 0.95rem; font-family: var(--font-body); outline: none; }
    .form-group input:focus { border-color: var(--pink); }
    .error { background: #fee2e2; color: #dc2626; padding: 0.6rem 1rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 1rem; }
    .full-btn { width: 100%; padding: 0.85rem; font-size: 1rem; }
  </style>
</head>
<body>
<div class="login-card">
  <img src="../assets/Logo.png" alt="Sugar Dips"/>
  <h1>Admin Login</h1>
  <p>Sugar Dips Admin Panel</p>
  <?php if ($error): ?>
  <div class="error">❌ <?= $error ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" placeholder="admin" required/>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required/>
    </div>
    <button type="submit" class="btn-pink full-btn">Login →</button>
  </form>
</div>
</body>
</html>