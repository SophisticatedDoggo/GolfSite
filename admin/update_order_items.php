<?php
require('auth_check.php');
require('../db.php');

$order_id    = (int) $_POST['order_id'];
$clubs_num   = (int) $_POST['clubs_num'];
$putters_num = (int) $_POST['putters_num'];

$stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

$item_stmt = $conn->prepare("
    INSERT INTO order_items (order_id, grip_id, quantity, unit_price)
    SELECT ?, ?, 1, (g.catalog_cost + mt.markup + pc.labor_cost + pc.material_cost)
    FROM grips g
    JOIN pricing_config pc ON pc.id = 1
    JOIN markup_tiers mt ON g.catalog_cost >= mt.min_price
        AND (mt.max_price IS NULL OR g.catalog_cost <= mt.max_price)
    WHERE g.id = ?
");

for ($i = 1; $i <= $clubs_num; $i++) {
    if (isset($_POST['club_grip_id_' . $i]) && (int) $_POST['club_grip_id_' . $i] > 0) {
        $grip_id = (int) $_POST['club_grip_id_' . $i];
        $item_stmt->bind_param("iii", $order_id, $grip_id, $grip_id);
        $item_stmt->execute();
    }
}

for ($i = 1; $i <= $putters_num; $i++) {
    if (isset($_POST['putter_grip_id_' . $i]) && (int) $_POST['putter_grip_id_' . $i] > 0) {
        $grip_id = (int) $_POST['putter_grip_id_' . $i];
        $item_stmt->bind_param("iii", $order_id, $grip_id, $grip_id);
        $item_stmt->execute();
    }
}

$item_stmt->close();

$total_result = $conn->query("SELECT COALESCE(SUM(unit_price), 0) AS total FROM order_items WHERE order_id = $order_id");
$order_total  = $total_result->fetch_assoc()['total'];

$total_stmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
$total_stmt->bind_param("di", $order_total, $order_id);
$total_stmt->execute();
$total_stmt->close();

$conn->close();

header("Location: order_detail.php?id=" . $order_id);
exit();
?>
