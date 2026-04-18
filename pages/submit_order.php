<?php
require('../db.php');

$cust_name = trim($_POST['cust_name']);
$cust_email = trim($_POST['cust_email']);
$cust_phone = trim($_POST['cust_phone']);
$cust_notes = trim($_POST['cust_notes']);
$clubs_num = trim($_POST['clubs_num']);
$putters_num = trim($_POST['putters_num']);

$stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, notes) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $cust_name, $cust_email, $cust_phone, $cust_notes);
$stmt->execute();
$stmt->close();

$order_id = $conn->insert_id;

$item_stmt = $conn->prepare("INSERT INTO order_items (order_id, grip_id, quantity) VALUES (?, ?, ?)");
for ($i=1; $i <= $clubs_num; $i++) {
    if (isset($_POST['club_grip_' . $i])) {
        $quantity = 1;
        $grip_id = $_POST['club_grip_id_' . $i];
        $item_stmt->bind_param("iii", $order_id, $grip_id, $quantity);
        $item_stmt->execute();
    }
}

for ($i=1; $i <= $putters_num; $i++) {
    if (isset($_POST['putter_grip_' . $i])) {
        $quantity = 1;
        $grip_id = $_POST['putter_grip_id_' . $i];
        $item_stmt->bind_param("iii", $order_id, $grip_id, $quantity);
        $item_stmt->execute();
    }
}
$item_stmt->close();
$conn->close();

require 'email_confirmation.php';
header("Location: order_confirmation.php");
exit();
?>