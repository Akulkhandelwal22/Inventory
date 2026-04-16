<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to perform this action.");
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Using your central connection file which now contains logActivity()
require 'db.php';

// 1. Get the ID from the URL
$id_to_delete = $_GET['id'];

// 2. FETCH the name first so we can log it (Before it's deleted!)
$fetchStmt = $pdo->prepare("SELECT name FROM products WHERE id = :id");
$fetchStmt->execute(['id' => $id_to_delete]);
$product = $fetchStmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    $product_name = $product['name'];

    // 3. SECURELY delete the row
    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id_to_delete]);

    // 4. LOG the action
    logActivity($pdo, 'DELETE', $product_name, "Product was permanently removed from inventory.");
}

// 5. Send the user back to the viewing page
header("Location: view.php");
exit();
?>