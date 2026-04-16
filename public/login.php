<?php
session_start();

// 1. Correct Path to Database
// Since login.php is in /public, we go up one level then into /config
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 2. Check if user exists and password is correct
    if ($user && password_verify($password, $user['password'])) {
        
        // 3. Start the session
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role']; // Store 'admin' or 'staff'

        // 4. Redirect to the dashboard
        // Since view.php is in the SAME folder (public/), no prefix is needed
        header("Location: view.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | IMS PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h3 class="text-center fw-bold mb-4">Admin Login</h3>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger py-2 small">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                            </div>
                            <button class="btn btn-primary w-100 fw-bold py-2">Sign In</button>
                        </form>
                    </div>
                </div>
                <p class="text-center text-secondary mt-3 small">&copy; 2026 IMS PRO System</p>
            </div>
        </div>
    </div>
</body>
</html>