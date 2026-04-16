<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit; }

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $new_sku = $_POST['item_sku']; // Capture the SKU from the form
    $new_name = $_POST['item_name'];
    $new_price = $_POST['item_price'];
    $new_qty = $_POST['item_quantity'];
    $new_cat = $_POST['item_category'];

    // 1. GET OLD DATA (Before the update)
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $old = $stmt->fetch();

    if ($old) {
        // 2. CHECK FOR CHANGES (Added SKU check here)
        $changes = [];
        if ($old['sku'] != $new_sku)     $changes[] = "SKU: '{$old['sku']}' → '$new_sku'";
        if ($old['name'] != $new_name)   $changes[] = "Name: '{$old['name']}' → '$new_name'";
        if ($old['price'] != $new_price) $changes[] = "Price: {$old['price']} → $$new_price";
        if ($old['quantity'] != $new_qty) $changes[] = "Qty: {$old['quantity']} → $new_qty";
        if ($old['category'] != $new_cat) $changes[] = "Cat: '{$old['category']}' → '$new_cat'";

        // 3. HANDLE IMAGE
        $image_name = $old['image']; 
        if (!empty($_FILES['product_image']['name'])) {
            $new_image_name = time() . "_" . $_FILES['product_image']['name'];
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], __DIR__ . "/uploads/" . $new_image_name)) {
                $image_name = $new_image_name;
                $changes[] = "Image updated";
            }
        }

        // 4. PERFORM THE UPDATE (Added sku = :sku)
        $sql = "UPDATE products SET sku = :sku, name = :n, price = :p, quantity = :q, category = :cat, image = :i WHERE id = :id";
        $updateStmt = $pdo->prepare($sql);
        $updateStmt->execute([
            'sku' => $new_sku, 
            'n' => $new_name, 
            'p' => $new_price, 
            'q' => $new_qty, 
            'cat' => $new_cat, 
            'i' => $image_name, 
            'id' => $id
        ]);

        // 5. LOG THE COMPARISON
        if (!empty($changes)) {
            $logDetails = implode(" | ", $changes);
            logActivity($pdo, 'UPDATE', $new_name, $logDetails);
        }
    }

    header("Location: view.php");
    exit();
}