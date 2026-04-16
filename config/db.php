<?php
// db.php - Central Database Configuration & Core Functions
$host    = '127.0.0.1';
$db      = 'test';
$user    = 'root';
$pass    = '';
$port    = '3307';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
     $pdo = new PDO($dsn, $user, $pass);
     // Force PDO to throw exceptions on errors
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     // Fetch as associative arrays by default
     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}

/**
 * 1. TRANSACTION LOGGING
 * Records specific stock movement (IN/OUT)
 */
function recordTransaction($pdo, $productId, $productName, $type, $qty, $reason) {
    try {
        $sql = "INSERT INTO stock_transactions (product_id, product_name, type, quantity, reason) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId, $productName, $type, $qty, $reason]);
    } catch (Exception $e) {
        error_log("Transaction recording failed: " . $e->getMessage());
    }
}
function logActivity($pdo, $type, $name, $details) {
    try {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // DEBUG: See if session has the user_id
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

        $sql = "INSERT INTO activity_log (user_id, action_type, product_name, details, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$userId, $type, $name, $details]);

        if (!$result) {
            // This will trigger if execute() returns false but doesn't throw an exception
            die("SQL Execution Failed: " . print_r($stmt->errorInfo(), true));
        }
        
    } catch (Exception $e) {
        // This will trigger for database connection or column errors
        die("LOGGING CRITICAL ERROR: " . $e->getMessage());
    }
}

/**
 * 3. LOW STOCK CHECKER
 * Triggers an alert log if stock is below threshold
 */
function checkLowStock($pdo, $productName) {
    $stmt = $pdo->prepare("SELECT quantity FROM products WHERE name = ?");
    $stmt->execute([$productName]);
    $product = $stmt->fetch();

    if ($product && $product['quantity'] < 5) {
        logActivity($pdo, 'ALERT', $productName, 'CRITICAL: Stock dropped below 5 units!');
        return true;
    }
    return false;
}
?>