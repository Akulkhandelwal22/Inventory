<?php
session_start();

// 1. Session and Permission Check
if (!isset($_SESSION['logged_in'])) { 
    header("Location: login.php"); 
    exit; 
}

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to perform this action.");
}

require '../config/db.php';
// 2. Fetch Product Data
if (!isset($_GET['id'])) { die("ID missing!"); }
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { die("Product not found!"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | IMS PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="view.php">📦 IMS PRO</a>
    </div>
</nav>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0 fw-bold text-center">Edit Product</h5>
                </div>
                <div class="card-body p-4">
                    <form action="../includes/update.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        
                        <div class="text-center mb-4">
                            <small class="text-muted d-block mb-2">Current Product Image</small>
                            <img src="uploads/<?= $product['image'] ?>" class="img-thumbnail shadow-sm" style="height: 120px; width: 120px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/120'">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Barcode / SKU</label>
                            <div class="input-group">
                                <input type="text" name="item_sku" id="item_sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>" required>
                                <button type="button" class="btn btn-secondary" onclick="toggleScanner()">📷 Scan</button>
                            </div>
                            <div id="reader" style="display: none;" class="mt-2 border rounded"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="item_category" class="form-select" required>
                                <?php 
                                $categories = ["Electronics", "Furniture", "Stationery", "Food & Beverage", "Other"];
                                foreach ($categories as $cat): 
                                    $selected = ($product['category'] == $cat) ? 'selected' : '';
                                ?>
                                    <option value="<?= $cat ?>" <?= $selected ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Stock Qty</label>
                                <input type="number" name="item_quantity" class="form-control" value="<?= $product['quantity'] ?>" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Price ($)</label>
                                <input type="number" step="0.01" name="item_price" class="form-control" value="<?= $product['price'] ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Change Image</label>
                            <input type="file" name="product_image" class="form-control">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning fw-bold py-2">Save Changes</button>
                            <a href="view.php" class="btn btn-light border">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let html5QrCode;
function toggleScanner() {
    const readerDiv = document.getElementById('reader');
    if (readerDiv.style.display === 'none' || readerDiv.style.display === '') {
        readerDiv.style.display = 'block';
        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" }, 
            { fps: 10, qrbox: { width: 250, height: 150 } },
            (decodedText) => {
                document.getElementById('item_sku').value = decodedText;
                document.getElementById('item_sku').classList.add('is-valid');
                stopScanner();
            }
        ).catch(err => alert("Camera error: " + err));
    } else {
        stopScanner();
    }
}
function stopScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            document.getElementById('reader').style.display = 'none';
            html5QrCode = null; 
        });
    }
}
</script>
</body>
</html>