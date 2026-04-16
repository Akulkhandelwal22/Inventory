<?php
session_start();
// Guard: Check if logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// 1. Using your central connection file
require 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['item_name'];
    $price = $_POST['item_price'];
    $quantity = $_POST['item_quantity']; 
    $category = $_POST['item_category']; 
   
    $image_name = "no-image.jpg"; 
    
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

    // ONLY ONE INSERT QUERY IS NEEDED
    // This query includes all columns: name, price, quantity, category, and image
    // Update your INSERT query in save.php
    $sku = $_POST['item_sku'];
    
    $sql = "INSERT INTO products (sku, name, price, quantity, category, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sku, $name, $price, $quantity, $category, $image_name]);

    // Log the activity once
    logActivity($pdo, 'ADD', $name, "Initial Stock: $quantity, Price: $$price, Category: $category");

    header("Location: view.php");
    exit();
}