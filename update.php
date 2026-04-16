<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['logged_in'])) { 
    header("Location: login.php"); 
    exit; 
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Validate ID
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        die("Error: Product ID is missing.");
    }

    $id = $_POST['id'];
    $new_sku = $_POST['item_sku']; 
    $new_name = $_POST['item_name'];
    $new_price = $_POST['item_price'];
    $new_qty = $_POST['item_quantity'];
    $new_cat = $_POST['item_category'];

    if (empty($new_sku)) {
        die("Error: SKU cannot be empty.");
    }

    // 3. Fetch current data for comparison
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $old = $stmt->fetch();

    if ($old) {
        $changes = [];
        if ($old['sku'] != $new_sku)      $changes[] = "SKU: '{$old['sku']}' → '$new_sku'";
        if ($old['name'] != $new_name)    $changes[] = "Name: '{$old['name']}' → '$new_name'";
        if ($old['price'] != $new_price)  $changes[] = "Price: {$old['price']} → $new_price";
        if ($old['quantity'] != $new_qty) $changes[] = "Qty: {$old['quantity']} → $new_qty";
        if ($old['category'] != $new_cat) $changes[] = "Cat: '{$old['category']}' → '$new_cat'";

        // 4. Handle Image Upload
        $image_name = $old['image']; 
        if (!empty($_FILES['product_image']['name'])) {
            $new_image_name = time() . "_" . $_FILES['product_image']['name'];
            $target_dir = __DIR__ . "/uploads/";
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_dir . $new_image_name)) {
                $image_name = $new_image_name;
                $changes[] = "Image updated";
                
                // Delete old image if it wasn't the default
                if ($old['image'] != "no-image.jpg" && file_exists($target_dir . $old['image'])) {
                    unlink($target_dir . $old['image']);
                }
            }
        }

        // 5. Database Update
        try {
            $sql = "UPDATE products SET sku = :sku, name = :n, price = :p, quantity = :q, category = :cat, image = :i WHERE id = :id";
            $updateStmt = $pdo->prepare($sql);
            $updateStmt->execute([
                ':sku' => $new_sku, 
                ':n'   => $new_name, 
                ':p'   => $new_price, 
                ':q'   => $new_qty, 
                ':cat' => $new_cat, 
                ':i'   => $image_name, 
                ':id'  => $id
            ]);

            // 6. Record Transactions & Logs
            if ($old['quantity'] != $new_qty) {
                $diff = $new_qty - $old['quantity'];
                $type = ($diff > 0) ? 'IN' : 'OUT';
                if (function_exists('recordTransaction')) {
                    recordTransaction($pdo, $id, $new_name, $type, abs($diff), "Manual edit");
                }
            }

            if (!empty($changes) && function_exists('logActivity')) {
                logActivity($pdo, 'UPDATE', $new_name, implode(" | ", $changes));
            }

            // Success Redirect
            header("Location: view.php?updated=1");
            exit();

        } catch (Exception $e) {
            error_log("Update failed: " . $e->getMessage());
            die("Database Error: " . $e->getMessage());
        }
    } else {
        die("Error: Product not found in database.");
    }
}