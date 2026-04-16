<?php
session_start();
require 'db.php';

// Security check
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// 1. Fetch all product data
$stmt = $pdo->query("SELECT id, name, category, quantity, price FROM products ORDER BY id ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Set headers to force download the file as a .csv
$filename = "inventory_report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// 3. Open the output stream
$output = fopen('php://output', 'w');

// 4. Set the Column Headers for the CSV file
fputcsv($output, ['ID', 'Product Name', 'Category', 'Quantity', 'Unit Price ($)']);

// 5. Loop through the products and add them to the CSV
foreach ($products as $row) {
    fputcsv($output, $row);
}

// 6. Log that an export happened
logActivity($pdo, 'EXPORT', 'System', 'Inventory data was exported to CSV.');

fclose($output);
exit();
?>