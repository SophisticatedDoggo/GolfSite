<?php
require('auth_check.php');
require('../db.php');

$username         = trim($_POST['username']);
$password         = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password !== $confirm_password) {
    header("Location: admins.php?error=mismatch");
    exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admin (username, password_hash) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hash);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: admins.php");
exit();
?>
