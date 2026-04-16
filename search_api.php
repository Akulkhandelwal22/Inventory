<?php
// Disable error reporting for the output so warnings don't break JSON
error_reporting(0);
header('Content-Type: application/json');

try {
    // Using your established connection details
    $dsn = "mysql:host=127.0.0.1;port=3307;dbname=test;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $q = $_GET['q'] ?? null;
    $cat = $_GET['cat'] ?? null;

    if ($cat) {
        // --- CATEGORY FILTER MODE ---
        // We use an exact match for the category buttons
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
        $stmt->execute([$cat]);
    } else {
        // --- SEARCH MODE ---
        // If q is empty, this returns all products. 
        // If q has text, it searches name and category.
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR category LIKE ? OR sku =?");
        $stmt->execute(["%$q%", "%$q%",$q]);
    }
    
    $data = $stmt->fetchAll();
    echo json_encode($data);

} catch (Exception $e) {
    // If it fails, send an empty array so the JavaScript loop doesn't break
    echo json_encode([]);
}
exit;