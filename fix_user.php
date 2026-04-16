<?php
require 'db.php';

$user = 'admin';
$pass = password_hash('admin123', PASSWORD_DEFAULT);

$pdo->query("TRUNCATE TABLE users"); // Clear old broken users
$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->execute([$user, $pass]);

echo "Success! User 'admin' created with password 'admin123'. <br>";
echo "The hash saved was: " . $pass;
?>