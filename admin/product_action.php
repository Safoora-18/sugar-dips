<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../db.php';

$action = $_POST['action'] ?? '';

// ── ADD ──────────────────────────────────────────────────────────────
if ($action === 'add') {
    $name         = trim($_POST['name']         ?? '');
    $price        = trim($_POST['price']        ?? '');
    $category     = trim($_POST['category']     ?? '');
    $description  = trim($_POST['description']  ?? '');
    $customizable = (int)($_POST['customizable'] ?? 0);

    if (!$name || !$price) {
        $_SESSION['error'] = 'Name and price are required.';
        header('Location: products.php'); exit;
    }

    if (empty($_FILES['image']['tmp_name']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Please choose an image to upload.';
        header('Location: products.php'); exit;
    }

    // Validate file type
    $allowed  = ['image/jpeg','image/png','image/gif','image/webp'];
    $mimeType = mime_content_type($_FILES['image']['tmp_name']);
    if (!in_array($mimeType, $allowed)) {
        $_SESSION['error'] = 'Only JPG, PNG, GIF or WEBP images allowed.';
        header('Location: products.php'); exit;
    }

    // Save into assets/ (same folder as existing product images)
    $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = 'assets/' . uniqid('product_') . '.' . $ext;
    $dest     = '../' . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $_SESSION['error'] = 'Could not save image — check assets/ folder permissions.';
        header('Location: products.php'); exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO products_new (name, description, price, image, category, customizable)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('ssdssi', $name, $description, $price, $filename, $category, $customizable);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "\"$name\" added successfully!";
    } else {
        unlink($dest);
        $_SESSION['error'] = 'Database error: ' . $conn->error;
    }

    header('Location: products.php'); exit;
}

// ── DELETE ───────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id    = (int)($_POST['id']    ?? 0);
    $image = $_POST['image'] ?? '';

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM products_new WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Only delete file if it was an uploaded one (not the original hardcoded assets)
        $imagePath = '../' . $image;
        if (str_contains($image, 'product_') && file_exists($imagePath)) {
            unlink($imagePath);
        }

        $_SESSION['msg'] = 'Product deleted.';
    }

    header('Location: products.php'); exit;
}

header('Location: products.php'); exit;