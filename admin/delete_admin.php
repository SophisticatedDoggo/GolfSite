<?php
require('auth_check.php');
require('../db.php');

$admin_id = (int) $_POST['admin_id'];

$stmt = $conn->prepare("SELECT username FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row && $row['username'] === $_SESSION['user']) {
    header("Location: admins.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: admins.php");
exit();
?>
