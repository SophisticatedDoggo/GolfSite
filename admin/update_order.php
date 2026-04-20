<?php
require('auth_check.php');
require('../db.php');

$order_id    = (int) $_POST['order_id'];
$cust_name   = trim($_POST['cust_name']);
$cust_email  = trim($_POST['cust_email']);
$cust_phone  = trim($_POST['cust_phone']);
$notes       = trim($_POST['notes']);
$status      = trim($_POST['status']);
$clubs_num   = (int) $_POST['clubs_num'];
$putters_num = (int) $_POST['putters_num'];

$stmt = $conn->prepare("UPDATE orders
                        SET customer_name = ?, customer_email = ?, customer_phone = ?, notes = ?, status = ?, clubs_num = ?, putters_num = ?
                        WHERE id = ?");
$stmt->bind_param("sssssiii", $cust_name, $cust_email, $cust_phone, $notes, $status, $clubs_num, $putters_num, $order_id);
$stmt->execute();
$stmt->close();

$item_count = $conn->query("SELECT COUNT(*) AS cnt FROM order_items WHERE order_id = $order_id")->fetch_assoc()['cnt'];

if ($item_count == 0) {
    $pricing = $conn->query("SELECT labor_cost, material_cost FROM pricing_config WHERE id = 1")->fetch_assoc();
    $total   = ($pricing['labor_cost'] + $pricing['material_cost']) * ($clubs_num + $putters_num);

    $total_stmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $total_stmt->bind_param("di", $total, $order_id);
    $total_stmt->execute();
    $total_stmt->close();
}

$conn->close();

header("Location: order_detail.php?id=" . $order_id);
exit();
?>