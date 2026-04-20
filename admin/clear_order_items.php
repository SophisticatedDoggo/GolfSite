<?php
require('auth_check.php');
require('../db.php');

$order_id = (int) $_POST['order_id'];

// Delete selected grips
$stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

// Mark order as own grips, keep counts
$stmt = $conn->prepare("UPDATE orders SET own_grips = 1 WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

// Recalculate labor/material total
$pricing = $conn->query("SELECT labor_cost, material_cost FROM pricing_config WHERE id = 1")->fetch_assoc();
$counts  = $conn->query("SELECT clubs_num, putters_num FROM orders WHERE id = $order_id")->fetch_assoc();
$total   = ($pricing['labor_cost'] + $pricing['material_cost']) * ((int)$counts['clubs_num'] + (int)$counts['putters_num']);

$total_stmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
$total_stmt->bind_param("di", $total, $order_id);
$total_stmt->execute();
$total_stmt->close();

$conn->close();

header("Location: order_detail.php?id=" . $order_id);
exit();
?>