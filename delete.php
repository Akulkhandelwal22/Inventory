<?php
session_start();

// 1. Guard: Check authentication first, then authorization
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to perform this action.");
}

// 2. Using your central connection file
require 'db.php';

// 3. Get the ID from the URL and validate it exists
if (isset($_GET['id'])) {
    $id_to_delete = $_GET['id'];

    // FETCH the data first (including image name) so we can clean up files and log it
    $fetchStmt = $pdo->prepare("SELECT name, image FROM products WHERE id = :id");
    $fetchStmt->execute(['id' => $id_to_delete]);
    $product = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $product_name = $product['name'];
        $image_to_remove = $product['image'];

        // SECURELY delete the row
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute(['id' => $id_to_delete])) {
            
            // Clean up the image file from the server to save space
            if ($image_to_remove != "no-image.jpg" && file_exists(__DIR__ . "/uploads/" . $image_to_remove)) {
                unlink(__DIR__ . "/uploads/" . $image_to_remove);
            }

            // LOG the action
            logActivity($pdo, 'DELETE', $product_name, "Product and associated image were permanently removed.");
        }
    }
}

// 4. Send the user back to the viewing page with a success flag
header("Location: view.php?deleted=1");
exit();
?>