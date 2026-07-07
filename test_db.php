<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sugardips', 3307);
if ($conn->connect_error) {
    die("FAILED: " . $conn->connect_error);
}
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    echo $row[0] . "<br>";
}
echo "PORT: " . $conn->server_info;
?>