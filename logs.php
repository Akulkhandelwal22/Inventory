<?php
session_start();

// Guard: Admin only
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: view.php?error=unauthorized");
    exit();
}

require 'db.php';

try {
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
    <style>
        /* Modern Color Palette */
        :root {
            --bg-body: #f4f7f9;
            --table-header: #ffffff;
            --text-main: #2d3436;
            --accent-blue: #0984e3;
        }

        body { 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            font-family: 'Inter', -apple-system, sans-serif; 
        }
        
        .main-container { padding: 40px 60px; }

        /* Modern Table Card */
        .table-wrapper {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e8ed;
        }

        .log-table {
            width: 100%;
            border-collapse: collapse;
        }

        .log-table thead th {
            background-color: var(--table-header);
            color: #636e72;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            padding: 18px 25px;
            font-weight: 600;
            border-bottom: 2px solid #f1f3f5;
            font-size: 0.8rem;
        }

        .log-table td {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f3f5;
            font-size: 0.92rem;
            vertical-align: middle;
        }

        .log-table tbody tr:hover {
            background-color: #fcfdfe;
            transition: 0.2s;
        }

        /* Styling for the Specific Columns */
        .col-id { color: #b2bec3; font-weight: 500; width: 80px; }
        
        /* Action Badges */
        .badge-log {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }
        .type-add { background: #e3fcef; color: #00a854; }
        .type-update { background: #e6f7ff; color: #1890ff; }
        .type-delete { background: #fff1f0; color: #f5222d; }
        .type-default { background: #f5f5f5; color: #595959; }

        .col-product { font-weight: 600; color: #2d3436; width: 200px; }
        
        .col-details { 
            font-family: 'Consolas', 'Monaco', monospace; 
            color: #636e72; 
            line-height: 1.6;
            white-space: pre-line;
            font-size: 0.85rem;
        }
        
        .col-date { color: #95a5a6; width: 220px; font-size: 0.85rem; }

        /* Navbar Refresh */
        .navbar { 
            background: #fff !important; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border-bottom: 1px solid #e1e8ed;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-light mb-4 py-3">
    <div class="container-fluid px-5">
        <a class="navbar-brand fw-bold text-primary" href="view.php">📦 IMS <span class="text-dark">PRO</span></a>
        <a href="view.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Back to Dashboard</a>
    </div>
</nav>

<div class="main-container">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Activity Audit Trail</h2>
        <p class="text-muted">Real-time history of inventory modifications</p>
    </div>

    <div class="table-wrapper">
        <div class="table-responsive">
            <table class="log-table">
                <thead>
                    <tr>
                        <th class="col-id"># ID</th>
                        <th>Action</th>
                        <th class="col-product">Product</th>
                        <th class="col-details">Details / Changes</th>
                        <th class="col-date text-end">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No activity records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): 
                            $typeClass = 'type-default';
                            $action = strtoupper($log['action_type']);
                            if ($action == 'ADD') $typeClass = 'type-add';
                            elseif ($action == 'UPDATE') $typeClass = 'type-update';
                            elseif ($action == 'DELETE') $typeClass = 'type-delete';
                        ?>
                        <tr>
                            <td class="col-id"><?= $log['id'] ?></td>
                            <td>
                                <span class="badge-log <?= $typeClass ?>">
                                    <?= $action ?>
                                </span>
                            </td>
                            <td class="col-product"><?= htmlspecialchars($log['product_name']) ?></td>
                            <td class="col-details"><?= htmlspecialchars($log['details']) ?></td>
                            <td class="col-date text-end"><?= date('D, M j, g:i A', strtotime($log['created_at'])) ?></td>
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