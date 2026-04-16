<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$price = (float)$_POST['price'];

if ($price > 500) {
    // Top tier discount
    $final = $price * 0.75; 
    echo "VIP Discount! Total: $" . $final;
} elseif ($price > 100) {
    // Standard discount
    $final = $price * 0.90;
    echo "10% Discount. Total: $" . $final;
} else {
    // No discount
    echo "Full Price: $" . $price;
}
?>