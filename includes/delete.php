<?php
session_start();

// 1. Guard: Check authentication
if (!isset($_SESSION['logged_in'])) {
    // FIX 1: If not logged in, go to LOGIN, not view.
    header("Location: ../public/login.php"); 
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to perform this action.");
}

// 2. Using your central connection file
require '../config/db.php';

// 3. Get the ID from the URL and validate it exists
if (isset($_GET['id'])) {
    $id_to_delete = $_GET['id'];

    $fetchStmt = $pdo->prepare("SELECT name, image FROM products WHERE id = :id");
    $fetchStmt->execute(['id' => $id_to_delete]);
    $product = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $product_name = $product['name'];
        $image_to_remove = $product['image'];

        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute(['id' => $id_to_delete])) {
            
            // Clean up the image file
            if ($image_to_remove != "no-image.jpg" && file_exists(__DIR__ . "/../uploads/" . $image_to_remove)) {
                unlink(__DIR__ . "/../uploads/" . $image_to_remove);
            }

            // LOG the action
            logActivity($pdo, 'DELETE', $product_name, "Product and associated image were permanently removed.");
            
            // FIX 2: Added a redirect here so the script stops immediately after a successful delete
            header("Location: ../public/view.php?deleted=1");
            exit();
        }
    }
}

// FIX 3: Corrected the path. Your code had "/..public/" which is invalid.
header("Location: ../public/view.php");
exit();
?>