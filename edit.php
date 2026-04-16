<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to perform this action.");
}
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit; }

// Use your central connection file
require 'db.php'; 

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { die("Product not found!"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-5">
        <div class="container">
            <a class="navbar-brand" href="view.php">Inventory Manager</a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-warning text-dark py-3">
                        <h5 class="mb-0">Edit Product: <?= htmlspecialchars($product['name']) ?></h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="update.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            
                            <div class="text-center mb-4">
                                <p class="small text-muted mb-1">Current Image</p>
                                <img src="uploads/<?= $product['image'] ?>" class="img-thumbnail shadow-sm" style="height: 120px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Barcode / SKU</label>
                                <div class="input-group">
                                    <input type="text" name="item_sku" id="item_sku" class="form-control" 
                                           value="<?= htmlspecialchars($product['sku']) ?>" placeholder="Scan or type barcode">
                                    <button type="button" class="btn btn-secondary" onclick="toggleScanner()">📷 Scan</button>
                                </div>
                            </div>

                            <div id="reader" style="width: 100%; max-width: 400px; display: none;" class="mt-2 border rounded"></div>

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
                                        // This line checks if the category matches what's in the DB
                                        $selected = ($product['category'] == $cat) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $cat ?>" <?= $selected ?>><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Stock Quantity</label>
                                <input type="number" name="item_quantity" class="form-control" value="<?= $product['quantity'] ?>" min="0" required>
                                <div class="form-text">Alerts trigger if this is below 5.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Price ($)</label>
                                <input type="number" step="0.01" name="item_price" class="form-control" value="<?= $product['price'] ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Update Image</label>
                                <input type="file" name="product_image" class="form-control">
                                <div class="form-text">Leave blank to keep current photo.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">Update Product</button>
                                <a href="view.php" class="btn btn-outline-secondary">Cancel</a>
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
                    stopScanner();
                    alert("Barcode Scanned: " + decodedText);
                },
                (errorMessage) => { /* scanning... */ }
            ).catch(err => alert("Camera error: " + err));
        } else {
            stopScanner();
        }
    }
    function stopScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                document.getElementById('reader').style.display = 'none';
                // Optional: clear the object so it can be restarted fresh
                html5QrCode = null; 
            }).catch(err => console.error("Failed to stop", err));
        }
    }
    </script>
</body>
</html>