<?php
session_start();

// 1. Security check: Must be logged in
if (!isset($_SESSION['logged_in'])) {
    // Corrected path to login.php
    header("Location: ../public/login.php");
    exit;
}

// 2. Corrected path to your database config
require '../config/db.php';

// 3. Fetch data and generate CSV
try {
    $stmt = $pdo->query("SELECT id, name, category, quantity, price FROM products ORDER BY id ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set filename
    $filename = "inventory_report_" . date('Y-m-d') . ".csv";

    // 4. Headers for CSV download
    // Ensure NO text or spaces are echoed before these header() calls
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    // 5. Open output stream
    $output = fopen('php://output', 'w');

    // 6. CSV Column Headers
    fputcsv($output, ['ID', 'Product Name', 'Category', 'Quantity', 'Unit Price ($)']);

    // 7. Data rows
    foreach ($products as $row) {
        fputcsv($output, $row);
    }

    // 8. Log the action (using the function from db.php)
    if (function_exists('logActivity')) {
        logActivity($pdo, 'EXPORT', 'System', 'Inventory data was exported to CSV.');
    }

    fclose($output);
    exit(); // Stop execution to prevent extra whitespace in CSV

} catch (Exception $e) {
    // If something goes wrong, redirect back to the dashboard with an error flag
    header("Location: ../public/view.php?error=export_failed");
    exit();
}
?>