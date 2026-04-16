<?php
session_start();
require 'db.php';

// Security check
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// 1. Fetch data
try {
    $stmt = $pdo->query("SELECT id, name, category, quantity, price FROM products ORDER BY id ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Set headers for download
    $filename = "inventory_report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    // 3. Open stream
    $output = fopen('php://output', 'w');

    // 4. Headers
    fputcsv($output, ['ID', 'Product Name', 'Category', 'Quantity', 'Unit Price ($)']);

    // 5. Data rows
    foreach ($products as $row) {
        fputcsv($output, $row);
    }

    // 6. Log the action
    // Note: Ensure your logActivity function is accessible here
    logActivity($pdo, 'EXPORT', 'System', 'Inventory data was exported to CSV.');

    fclose($output);
    exit();

} catch (Exception $e) {
    // If something goes wrong before headers are sent
    header('Content-Type: text/html');
    die("Export failed: " . $e->getMessage());
}
?>