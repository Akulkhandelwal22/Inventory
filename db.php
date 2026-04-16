<?php
// db.php - The single source of truth for your database connection
$host = '127.0.0.1';
$db   = 'test';
$user = 'root';
$pass = '';
$port = '3307';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
     $pdo = new PDO($dsn, $user, $pass);
     // Set error mode to exceptions so we see mistakes immediately
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}

/**
 * Helper function to record actions in the activity_log table.
 * Usage: logActivity($pdo, 'ADD', 'Laptop', 'Added with 10 units');
 */
function logActivity($pdo, $type, $name, $details) {
    try {
        $sql = "INSERT INTO activity_log (action_type, product_name, details) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$type, $name, $details]);
    } catch (Exception $e) {
        // We use a silent fail or error_log here so a logging error 
        // doesn't stop the main app from working.
        error_log("Logging failed: " . $e->getMessage());
    }
}
?>