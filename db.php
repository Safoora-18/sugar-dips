<?php
$host = '127.0.0.1';   // ← changed from 'localhost'
$user = 'root';
$pass = '';
$db   = 'sugardips';

$conn = new mysqli($host, $user, $pass, $db, 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>