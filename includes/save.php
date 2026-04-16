<?php
session_start();

// Guard: Check if logged in - Unauthorized users go to login
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../public/login.php");
    exit;
}

// 1. Path to central connection is correct
require '../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['item_name'];
    $price    = $_POST['item_price'];
    $quantity = $_POST['item_quantity']; 
    $category = $_POST['item_category']; 
    $sku      = $_POST['item_sku'];
   
    $image_name = "no-image.jpg"; 
    
    // Handle Image Upload
    if (!empty($_FILES['product_image']['name'])) {
        // Sanitize filename to prevent issues with spaces/special chars
        $image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['product_image']['name']);
        $upload_dir = __DIR__ . "/../uploads/"; // Correctly moves up from /includes to /uploads
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $image_name)) {
            die("Error: PHP could not move the file to $upload_dir. Check folder permissions.");
        }
    }

    // 2. Insert into the database
    try {
        $sql = "INSERT INTO products (sku, name, price, quantity, category, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sku, $name, $price, $quantity, $category, $image_name]);

        // 3. Log the activity 
        // This uses the function defined in your config/db.php
        $logDetails = "SKU: $sku | Initial Stock: $quantity | Price: $$price | Category: $category";
        logActivity($pdo, 'ADD', $name, $logDetails);

        // Success redirect back to public dashboard
        header("Location: ../public/view.php?success=1");
        exit();

    } catch (Exception $e) {
        error_log("Insert failed: " . $e->getMessage());
        die("Database Error: " . $e->getMessage());
    }
}
?>