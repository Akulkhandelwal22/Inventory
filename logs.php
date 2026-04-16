<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    // If they aren't an admin, kick them back to the dashboard
    header("Location: view.php?error=unauthorized");
    exit();
}

require 'db.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Fetch the 50 most recent actions
$stmt = $pdo->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 50");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs | IMS PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .log-details { font-family: 'Courier New', monospace; font-size: 0.85rem; }
        .badge-add { background-color: #198754; }    /* Green */
        .badge-update { background-color: #0d6efd; } /* Blue */
        .badge-delete { background-color: #dc3545; } /* Red */
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="view.php">📦 IMS PRO</a>
        <a href="view.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">System Activity Audit</h2>
        <span class="text-muted small">Showing last 50 actions</span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 15%;">Timestamp</th>
                        <th style="width: 10%;">Action</th>
                        <th style="width: 20%;">Product</th>
                        <th>Details (Changes)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No activity recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-muted small">
                                <?= date('M d, g:i A', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower($log['action_type']) ?> w-100">
                                    <?= $log['action_type'] ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($log['product_name']) ?></td>
                            <td class="log-details text-secondary">
                                <?= htmlspecialchars($log['details']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>