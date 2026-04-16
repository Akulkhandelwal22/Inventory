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
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

/**
 * 2. ACTIVITY LOGGING
 * Records general user actions (Update, Delete, Login, etc.)
 */
function logActivity($pdo, $type, $name, $details) {
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Use user_id from session or 0 if guest
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

        $sql = "INSERT INTO activity_log (user_id, action_type, product_name, details, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $type, $name, $details]);
        
    } catch (Exception $e) {
        error_log("Logging failed: " . $e->getMessage());
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
        // Log a specialized alert entry
        logActivity($pdo, 'ALERT', $productName, 'CRITICAL: Stock dropped below 5 units!');
        return true;
    }
    return false;
}
?>