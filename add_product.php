<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$dsn = "mysql:host=127.0.0.1;port=3307;dbname=test;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");

// This is a "Prepared Statement" - The SECURE way to save data
$sql = "INSERT INTO products (name, price) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute(["Laptop", 999.99]);

echo "Product added successfully!";
?>