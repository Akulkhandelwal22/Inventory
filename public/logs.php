<?php
session_start();

// Guard: Admin only
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/view.php?error=unauthorized");
    exit();
}

// Path to db.php in the config folder
require '../config/db.php';

try {
    // Fetching the last 50 entries
    $stmt = $pdo->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 50");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("LOGGING ERROR: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Audit | IMS PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f4f7f9;
            --table-header: #ffffff;
            --text-main: #2d3436;
            --accent-blue: #0984e3;
        }
        body { background-color: var(--bg-body); color: var(--text-main); font-family: 'Inter', sans-serif; }
        .main-container { padding: 40px 60px; }
        .table-wrapper { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); border: 1px solid #e1e8ed; }
        .log-table { width: 100%; border-collapse: collapse; }
        .log-table thead th { background-color: var(--table-header); color: #636e72; text-transform: uppercase; padding: 18px 25px; font-weight: 600; border-bottom: 2px solid #f1f3f5; font-size: 0.8rem; }
        .log-table td { padding: 20px 25px; border-bottom: 1px solid #f1f3f5; font-size: 0.92rem; vertical-align: middle; }
        .badge-log { padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; display: inline-block; }
        .type-add { background: #e3fcef; color: #00a854; }
        .type-update { background: #e6f7ff; color: #1890ff; }
        .type-delete { background: #fff1f0; color: #f5222d; }
        .type-alert { background: #fffbe6; color: #faad14; }
        .type-default { background: #f5f5f5; color: #595959; }
        .col-product { font-weight: 600; }
        .col-details { font-family: 'Courier New', monospace; color: #636e72; font-size: 0.85rem; word-break: break-word; }
        .navbar { background: #fff !important; border-bottom: 1px solid #e1e8ed; }
        .user-pill { background: #f1f3f5; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; color: #495057; }
    </style>
</head>
<body>

<nav class="navbar navbar-light mb-4 py-3">
    <div class="container-fluid px-5">
        <a class="navbar-brand fw-bold text-primary" href="../public/view.php">📦 IMS <span class="text-dark">PRO</span></a>
        <a href="../public/view.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Back to Dashboard</a>
    </div>
</nav>

<div class="main-container">
    <div class="mb-4 d-flex justify-content-between align-items-end">
        <div>
            <h2 class="fw-bold mb-1">Activity Audit Trail</h2>
            <p class="text-muted mb-0">Full history of system and inventory modifications</p>
        </div>
        <div class="text-end small text-muted">
            Last Updated: <?= date('H:i:s') ?>
        </div>
    </div>

    <div class="table-wrapper">
        <div class="table-responsive">
            <table class="log-table">
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Product</th>
                        <th>Details / Changes</th>
                        <th class="text-end">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No activity records found in the database.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): 
                            $typeClass = 'type-default';
                            $action = strtoupper($log['action_type']);
                            if ($action == 'ADD') $typeClass = 'type-add';
                            elseif ($action == 'UPDATE') $typeClass = 'type-update';
                            elseif ($action == 'DELETE') $typeClass = 'type-delete';
                            elseif ($action == 'ALERT') $typeClass = 'type-alert';
                        ?>
                        <tr>
                            <td><span class="text-muted">#</span><?= $log['id'] ?></td>
                            <td><span class="user-pill">UID: <?= $log['user_id'] ?? '0' ?></span></td>
                            <td><span class="badge-log <?= $typeClass ?>"><?= $action ?></span></td>
                            <td class="col-product"><?= htmlspecialchars($log['product_name'] ?? 'N/A') ?></td>
                            <td class="col-details"><?= htmlspecialchars($log['details']) ?></td>
                            <td class="text-end text-secondary small"><?= date('D, M j, g:i A', strtotime($log['created_at'])) ?></td>
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