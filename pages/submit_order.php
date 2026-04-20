<?php
require('../db.php');

$cust_name = trim($_POST['cust_name']);
$cust_email = trim($_POST['cust_email']);
$cust_phone = trim($_POST['cust_phone']);
$cust_notes = trim($_POST['cust_notes']);
$clubs_num = trim($_POST['clubs_num']);
$putters_num = trim($_POST['putters_num']);

$stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, notes, clubs_num, putters_num) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssii", $cust_name, $cust_email, $cust_phone, $cust_notes, $clubs_num, $putters_num);
$stmt->execute();
$stmt->close();

$order_id = $conn->insert_id;

$provide_grips = $_POST['provide_grips'];

if ($provide_grips === 'no') {
    $item_stmt = $conn->prepare("
        INSERT INTO order_items (order_id, grip_id, quantity, unit_price)
        SELECT ?, ?, 1, (g.catalog_cost + mt.markup + pc.labor_cost + pc.material_cost)
        FROM grips g
        JOIN pricing_config pc ON pc.id = 1
        JOIN markup_tiers mt ON g.catalog_cost >= mt.min_price
            AND (mt.max_price IS NULL OR g.catalog_cost <= mt.max_price)
        WHERE g.id = ?
    ");
    for ($i=1; $i <= $clubs_num; $i++) {
        if (isset($_POST['club_grip_' . $i])) {
            $grip_id = (int) $_POST['club_grip_id_' . $i];
            $item_stmt->bind_param("iii", $order_id, $grip_id, $grip_id);
            $item_stmt->execute();
        }
    }
    for ($i=1; $i <= $putters_num; $i++) {
        if (isset($_POST['putter_grip_' . $i])) {
            $grip_id = (int) $_POST['putter_grip_id_' . $i];
            $item_stmt->bind_param("iii", $order_id, $grip_id, $grip_id);
            $item_stmt->execute();
        }
    }
    $item_stmt->close();

    $detail_stmt = $conn->prepare("
        SELECT g.brand, g.model, g.size, g.color,
               (g.catalog_cost + mt.markup) AS grip_price,
               pc.labor_cost, pc.material_cost,
               (g.catalog_cost + mt.markup + pc.labor_cost + pc.material_cost) AS total_per_grip
        FROM order_items oi
        JOIN grips g ON oi.grip_id = g.id
        JOIN pricing_config pc ON pc.id = 1
        JOIN markup_tiers mt ON g.catalog_cost >= mt.min_price
            AND (mt.max_price IS NULL OR g.catalog_cost <= mt.max_price)
        WHERE oi.order_id = ?
    ");
    $detail_stmt->bind_param("i", $order_id);
    $detail_stmt->execute();
    $items_detail = $detail_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $detail_stmt->close();

    $order_total = array_sum(array_column($items_detail, 'total_per_grip'));
    $labor_cost  = $items_detail[0]['labor_cost'] ?? 0;
    $material_cost = $items_detail[0]['material_cost'] ?? 0;
} else {
    $items_detail  = [];
    $total_clubs   = (int)$clubs_num + (int)$putters_num;
    $labor_stmt    = $conn->prepare("SELECT labor_cost, material_cost FROM pricing_config WHERE id = 1");
    $labor_stmt->execute();
    $labor_row     = $labor_stmt->get_result()->fetch_assoc();
    $labor_cost    = $labor_row['labor_cost'] ?? 0;
    $material_cost = $labor_row['material_cost'] ?? 0;
    $order_total   = ($labor_cost + $material_cost) * $total_clubs;
    $labor_stmt->close();
}

$update_stmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
$update_stmt->bind_param("di", $order_total, $order_id);
$update_stmt->execute();
$update_stmt->close();

$conn->close();

require 'email_confirmation.php';
header("Location: order_confirmation.php");
exit();
?>