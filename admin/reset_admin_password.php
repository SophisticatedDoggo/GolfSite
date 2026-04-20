<?php
require('auth_check.php');
require('../db.php');

$admin_id         = (int) $_POST['admin_id'];
$new_password     = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if ($new_password !== $confirm_password) {
    header("Location: admins.php?reset=" . $admin_id . "&error=mismatch");
    exit();
}

$hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admin SET password_hash = ? WHERE id = ?");
$stmt->bind_param("si", $hash, $admin_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: admins.php");
exit();
?>
