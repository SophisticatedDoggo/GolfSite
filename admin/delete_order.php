<?php
require('auth_check.php');
require('../db.php');

$order_id = (int) $_POST['order_id'];

$stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: orders.php");
exit();
?>
