<?php
// Disable error reporting for the output so warnings don't break JSON
error_reporting(0);
header('Content-Type: application/json');

// 1. Load the central connection
// This gives us the $pdo object automatically
require '../config/db.php';

try {
    // 2. Remove the manual $dsn and $pdo lines that were here.
    // They are no longer needed because of the require above.

    $q = $_GET['q'] ?? null;
    $cat = $_GET['cat'] ?? null;

    if ($cat) {
        // --- CATEGORY FILTER MODE ---
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
        $stmt->execute([$cat]);
    } else {
        // --- SEARCH MODE ---
        // If q is empty, this returns all products. 
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR category LIKE ? OR sku = ?");
        $stmt->execute(["%$q%", "%$q%", $q]);
    }
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (Exception $e) {
    // If it fails, send an empty array so the JavaScript doesn't crash
    echo json_encode([]);
}
exit;