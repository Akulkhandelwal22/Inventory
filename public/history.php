<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit; }

$stmt = $pdo->query("SELECT * FROM stock_transactions ORDER BY created_at DESC LIMIT 50");
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IMS | Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container"><a class="navbar-brand" href="view.php">📦 IMS PRO</a></div>
    </nav>
    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark">Stock Movement History</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $t): ?>
                        <tr>
                            <td class="small text-muted"><?= $t['created_at'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($t['product_name']) ?></td>
                            <td>
                                <span class="badge <?= $t['type'] == 'IN' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $t['type'] ?>
                                </span>
                            </td>
                            <td><?= $t['quantity'] ?> units</td>
                            <td class="text-muted small"><?= htmlspecialchars($t['reason']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>