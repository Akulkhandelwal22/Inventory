<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require '../config/db.php'; 

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products");
$all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_inventory_value = 0;
$low_stock_count = 0;
$total_items = count($all_products);

foreach($all_products as $p) { 
    $total_inventory_value += ($p['price'] * $p['quantity']); 
    if ($p['quantity'] < 5) {
        $low_stock_count++;
    }
}
$max_price = $total_items > 0 ? max(array_column($all_products, 'price')) : 0;

// Fetch category counts for the chart
$cat_stmt = $pdo->query("SELECT category, COUNT(*) as count FROM products GROUP BY category");
$chart_data = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = json_encode(array_column($chart_data, 'category'));
$counts = json_encode(array_column($chart_data, 'count'));

// Fetch logs directly for the integrated view
$log_stmt = $pdo->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 50");
$logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS | Smart Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .main-content { padding: 15px; }
        .badge-quantity { font-size: 0.85rem; padding: 0.4em 0.7em; }
        .low-stock-row { background-color: #fff3f3 !important; border-left: 4px solid #dc3545; }
        tbody tr:hover { background-color: #f8f9fa !important; transition: 0.2s; }
        .header-actions .btn { margin-left: 5px; }
        #logSection { border-top: 3px solid #0d6efd; transition: 0.3s; }
        .log-table-container { max-height: 400px; overflow-y: auto; }

        @media (max-width: 768px) {
        .log-table-container {
            max-height: 300px; /* Shorter height on mobile */
        }
        .header-actions .btn {
            margin-bottom: 5px;
            width: 48%; /* Side-by-side buttons on mobile */
            font-size: 0.8rem;
        }
    }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark shadow-sm sticky-top mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="view.php">📦 IMS PRO</a>
        <div class="d-flex align-items-center">
            <span class="text-white-50 me-3 small">Logged in as: <?= htmlspecialchars($_SESSION['role']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container-xl main-content">
    <div class="row align-items-center mb-4 g-3">
        <div class="col-12 col-lg-6 text-center text-lg-start">
            <h2 class="h4 mb-0 text-dark fw-bold">Live Inventory</h2>
            <p class="text-muted small mb-0">Manage and track your stock in real-time</p>
        </div>
        <div class="col-12 col-lg-6">
            <div class="d-flex flex-wrap justify-content-center justify-content-lg-end gap-2">
                <button onclick="toggleChart()" class="btn btn-outline-primary btn-sm px-3 shadow-sm">📈 Analytics</button>
                <button onclick="confirmDownload()" class="btn btn-outline-success btn-sm px-3 shadow-sm">📊 Export CSV</button>
                <button onclick="toggleLogs()" class="btn btn-outline-dark btn-sm px-3 shadow-sm">📜 View Logs</button>
                <a href="index.html" class="btn btn-success btn-sm px-3 shadow-sm">+ Add Product</a>
        </div>

    <div id="chartSection" class="col-md-12 mb-4" style="display: none;">
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-3 text-primary">📦 Stock Distribution by Category</h5>
                <button type="button" class="btn-close mb-3" onclick="toggleChart()"></button>
            </div>
            <div style="height: 300px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-2 p-md-3">
                    <small class="opacity-75">Unique Items</small>
                    <div class="h4 fw-bold mb-0"><?= $total_items ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm <?= $low_stock_count > 0 ? 'bg-danger' : 'bg-secondary' ?> text-white h-100">
                <div class="card-body p-3">
                    <small class="opacity-75">Low Stock Alert</small>
                    <div class="h3 fw-bold mb-0"><?= $low_stock_count ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3">
                    <small class="opacity-75">Top Unit Price</small>
                    <div class="h3 fw-bold mb-0">$<?= number_format($max_price, 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0">🔍</span>
                        <input type="text" id="ajaxSearch" class="form-control border-0 shadow-none" placeholder="Search product name...">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dropdown h-100">
                <button class="btn btn-white bg-white w-100 shadow-sm dropdown-toggle border-0 py-2" type="button" id="categoryDropdown" data-bs-toggle="dropdown">
                    📂 Filter Category
                </button>
                <ul class="dropdown-menu shadow border-0 w-100">
                    <li><a class="dropdown-item filter-opt" href="#" data-category="all">All Items</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item filter-opt" href="#" data-category="Electronics">Electronics</a></li>
                    <li><a class="dropdown-item filter-opt" href="#" data-category="Furniture">Furniture</a></li>
                    <li><a class="dropdown-item filter-opt" href="#" data-category="Stationery">Stationery</a></li>
                    <li><a class="dropdown-item filter-opt" href="#" data-category="Food & Beverage">Food & Beverage</a></li>
                    <li><a class="dropdown-item filter-opt" href="#" data-category="Other">Other</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0"style="min-width: 650px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 40%;">Product Detail</th>
                        <th style="width: 20%;">Stock Level</th>
                        <th style="width: 20%;">Unit Price</th>
                        <th class="text-end pe-3" style="width: 20%;">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <?php foreach ($all_products as $row): ?>
                    <tr class="<?= $row['quantity'] < 5 ? 'low-stock-row' : '' ?>">
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" class="product-img me-3 border" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>&background=random';">
                                <div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                                    <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($row['category']) ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-quantity <?= $row['quantity'] < 5 ? 'bg-danger' : 'bg-dark' ?>">
                                <?= $row['quantity'] ?> in stock
                            </span>
                        </td>
                        <td class="fw-bold text-success">$<?= number_format($row['price'], 2) ?></td>
                        <td class="text-end pe-3">
                            <div class="btn-group">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="../includes/delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">Delete</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


    <div id="logSection" class="col-md-12 mt-4 mb-5" style="display: none;">
        <div class="card border-0 shadow-lg p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark mb-0">📜 Activity Audit Trail</h4>
                <button type="button" class="btn-close" onclick="toggleLogs()"></button>
            </div>
            <div class="log-table-container border rounded bg-light" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Action</th>
                            <th>Product</th>
                            <th>Details</th>
                            <th class="text-end">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($log['action_type']) ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($log['product_name']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($log['details']) ?></td>
                                <td class="text-end small"><?= date('M j, H:i', strtotime($log['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const IS_ADMIN = <?= ($_SESSION['role'] === 'admin') ? 'true' : 'false' ?>;

// Toggle Activity Logs
function toggleLogs() {
    const logDiv = document.getElementById('logSection');
    if (logDiv.style.display === 'none') {
        logDiv.style.display = 'block';
        logDiv.scrollIntoView({ behavior: 'smooth' });
    } else {
        logDiv.style.display = 'none';
    }
}
// Analytics Toggle
let myChart = null;
function toggleChart() {
    const chartDiv = document.getElementById('chartSection');
    chartDiv.style.display = (chartDiv.style.display === 'none') ? 'block' : 'none';
    if(chartDiv.style.display === 'block') renderInventoryChart();
}

function renderInventoryChart() {
    if (myChart !== null) return;
    const ctx = document.getElementById('categoryChart').getContext('2d');
    myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= $labels ?>,
            datasets: [{
                label: 'Items',
                data: <?= $counts ?>,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                borderRadius: 6
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}

// CSV Export
function confirmDownload() {
    Swal.fire({
        title: 'Generate CSV?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Download'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = '../includes/export.php';
    });
}

// Search & Filter Logic (AJAX)
function updateTable(data) {
    let body = document.getElementById('productTableBody');
    let html = '';
    if (!data || data.length === 0) {
        body.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">No items found.</td></tr>';
        return;
    }
    data.forEach(item => {
        let lowStockClass = item.quantity < 5 ? 'low-stock-row' : '';
        let badgeClass = item.quantity < 5 ? 'bg-danger' : 'bg-dark';
        let deleteBtn = IS_ADMIN ? `<a href="../includes/delete.php?id=${item.id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">Delete</a>` : '';
        html += `
        <tr class="${lowStockClass}">
            <td class="ps-3">
                <div class="d-flex align-items-center">
                    <img src="../uploads/${item.image}" class="product-img me-3 border" onerror="this.src='https://via.placeholder.com/50'">
                    <div>
                        <div class="fw-semibold text-dark">${item.name}</div>
                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;">${item.category}</span>
                    </div>
                </div>
            </td>
            <td><span class="badge badge-quantity ${badgeClass}">${item.quantity} in stock</span></td>
            <td class="fw-bold text-success">$${parseFloat(item.price).toFixed(2)}</td>
            <td class="text-end pe-3">
                <div class="btn-group"><a href="edit.php?id=${item.id}" class="btn btn-sm btn-outline-primary">Edit</a>${deleteBtn}</div>
            </td>
        </tr>`;
    });
    body.innerHTML = html;
}

document.getElementById('ajaxSearch').addEventListener('input', function() {
    fetch('../includes/search_api.php?q=' + encodeURIComponent(this.value))
        .then(res => res.json()).then(data => updateTable(data));
});

document.querySelectorAll('.filter-opt').forEach(opt => {
    opt.addEventListener('click', function(e) {
        e.preventDefault();
        let cat = this.getAttribute('data-category');
        document.getElementById('categoryDropdown').innerText = (cat === 'all') ? '📂 All Items' : '📂 ' + cat;
        let url = (cat === 'all') ? '../includes/search_api.php?q=' : '../includes/search_api.php?cat=' + encodeURIComponent(cat);
        fetch(url).then(res => res.json()).then(data => updateTable(data));
    });
});
</script>
</body>
</html>