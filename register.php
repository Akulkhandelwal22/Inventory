<?php
$dsn = "mysql:host=127.0.0.1;port=3307;dbname=test;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$user, $pass]);
    echo "User registered! <a href='login.php'>Login here</a>";
}
?>
<form method="POST">
    <input type="text" name="username" placeholder="New Username" required>
    <input type="password" name="password" placeholder="New Password" required>
    <button type="submit">Register</button>
</form>