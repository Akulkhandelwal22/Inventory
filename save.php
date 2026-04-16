<?php
session_start();

// Guard: Check if logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// 1. Using your central connection file (which contains logActivity)
require 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['item_name'];
    $price    = $_POST['item_price'];
    $quantity = $_POST['item_quantity']; 
    $category = $_POST['item_category']; 
    $sku      = $_POST['item_sku'];
   
    $image_name = "no-image.jpg"; 
    
    // Handle Image Upload
    if (!empty($_FILES['product_image']['name'])) {
        $image_name = time() . "_" . $_FILES['product_image']['name'];
        $upload_dir = __DIR__ . "/uploads/";
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $image_name)) {
            die("Error: PHP could not move the file.");
        }
    }

    // 2. Insert into the database using PDO
    try {
        $sql = "INSERT INTO products (sku, name, price, quantity, category, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sku, $name, $price, $quantity, $category, $image_name]);

        // 3. Log the activity - Professional and traceable
        // We include the SKU and initial stock in the details
        $logDetails = "SKU: $sku | Initial Stock: $quantity | Price: $price | Category: $category";
        logActivity($pdo, 'ADD', $name, $logDetails);

        header("Location: view.php?success=1");
        exit();

    } catch (Exception $e) {
        // Log the error to your system log if the insert fails
        error_log("Insert failed: " . $e->getMessage());
        //die("Error: Could not save product.");
        die("Database Error: " . $e->getMessage());
    }
}
?>