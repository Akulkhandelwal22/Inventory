<?php
session_start();
require 'db.php'; 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS | Smart Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .main-content { padding: 15px; }
        .low-stock-row { background-color: #fff3f3 !important; }
        .badge-quantity { font-size: 0.85rem; padding: 0.4em 0.7em; }
        @media (max-width: 576px) {
            .btn-group { display: flex; flex-direction: column; gap: 5px; }
        }
        /* Makes the low stock rows have a subtle red pulse or glow */
        .low-stock-row {
            background-color: #fff3f3 !important;
            border-left: 4px solid #dc3545; /* Red indicator on the left */
        }
        
        /* Hover effect to make rows feel interactive */
        tbody tr:hover {
            background-color: #355575 !important;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark shadow-sm sticky-top mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="view.php">📦 IMS PRO</a>
        <div class="d-flex align-items-center">
            <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container main-content">
    <div class="row align-items-center mb-4">
        <div class="col-6">
            <h2 class="h4 mb-0 text-dark">Live Inventory</h2>
        </div>
        <div class="col-6 text-end">
            <a href="export.php" class="btn btn-outline-success shadow-sm me-2">📊 Export CSV</a>
            <a href="logs.php" class="btn btn-outline-dark shadow-sm me-2">📜 View Logs</a>
            <a href="index.html" class="btn btn-success shadow-sm">+ Add Product</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-3">
                    <small class="opacity-75">Unique Items</small>
                    <div class="h3 fw-bold mb-0"><?= $total_items ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body p-3">
                    <small class="opacity-75">Stock Value</small>
                    <div class="h3 fw-bold mb-0">$<?= number_format($total_inventory_value, 2) ?></div>
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
    <div class="dropdown mb-3">
    <button class="btn btn-dark dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        📂 Filter by Category
    </button>
    <ul class="dropdown-menu shadow border-0" aria-labelledby="categoryDropdown">
        <li><a class="dropdown-item filter-opt" href="#" data-category="all">All Items</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item filter-opt" href="#" data-category="Electronics">Electronics</a></li>
        <li><a class="dropdown-item filter-opt" href="#" data-category="Furniture">Furniture</a></li>
        <li><a class="dropdown-item filter-opt" href="#" data-category="Stationery">Stationery</a></li>
        <li><a class="dropdown-item filter-opt" href="#" data-category="Food & Beverage">Food</a></li>
        <li><a class="dropdown-item filter-opt" href="#" data-category="Other">Other</a></li>
    </ul>
</div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0">🔍</span>
                <input type="text" id="ajaxSearch" class="form-control border-0 shadow-none" placeholder="Search inventory...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Product Detail</th>
                        <th>Stock Level</th>
                        <th>Unit Price</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody" class="border-top-0">
                    <?php foreach ($all_products as $row): ?>
                    <tr class="<?= $row['quantity'] < 5 ? 'low-stock-row' : '' ?>">
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <img src="uploads/<?= $row['image'] ?>" class="product-img me-3 border">
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
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">Delete</a>
                            <?php endif; ?>
                           </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// 1. Helper Function to Redraw the Table (Prevents Duplication)
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
        
        html += `
        <tr class="${lowStockClass}">
            <td class="ps-3">
                <div class="d-flex align-items-center">
                    <img src="uploads/${item.image}" class="product-img me-3 border">
                    <div>
                        <div class="fw-semibold text-dark">${item.name}</div>
                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;">
                            ${item.category}
                        </span>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge badge-quantity ${badgeClass}">${item.quantity} in stock</span>
            </td>
            <td class="fw-bold text-success">$${parseFloat(item.price).toFixed(2)}</td>
            <td class="text-end pe-3">
                <div class="btn-group">
                    <a href="edit.php?id=${item.id}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="delete.php?id=${item.id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">Delete</a>
                </div>
            </td>
        </tr>`;
    });
    body.innerHTML = html;
}

// 2. Live Search Event
document.getElementById('ajaxSearch').addEventListener('input', function() {
    let query = this.value;
    fetch('search_api.php?q=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => updateTable(data))
        .catch(err => console.error('Search Error:', err));
});

// 3. Category Filter Event
document.querySelectorAll('.filter-opt').forEach(opt => {
    opt.addEventListener('click', function(e) {
        e.preventDefault(); // Stop the page from jumping
        
        let category = this.getAttribute('data-category');
        let dropdownBtn = document.getElementById('categoryDropdown');
        
        // Update the Dropdown Button Text
        dropdownBtn.innerText = (category === 'all') ? '📂 Filter by Category' : '📂 ' + category;
        
        // Use your existing search_api logic
        let url = (category === 'all') ? 'search_api.php?q=' : 'search_api.php?cat=' + encodeURIComponent(category);

        fetch(url)
            .then(res => res.json())
            .then(data => updateTable(data)) // Reuses your existing updateTable function
            .catch(err => console.error('Filter Error:', err));
    });
});
</script>
</script>
</body>
</html>