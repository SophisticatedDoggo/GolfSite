<?php
require('auth_check.php');
require('../db.php');

$order_id = (int) $_POST['order_id'];
$clubs_num = (int) $_POST['clubs_num'];
$putters_num = (int) $_POST['putters_num'];

$swing_grip_result = $conn->query("
        SELECT id FROM grips
        WHERE category = 'swing' LIMIT 1;
    ");
$club_result = $swing_grip_result->fetch_assoc();

$putter_grip_result = $conn->query("
        SELECT id FROM grips
        WHERE category = 'putter' LIMIT 1;
    ");
$putter_result = $putter_grip_result->fetch_assoc();

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
        $item_stmt->bind_param("iii", $order_id, $club_result['id'], $club_result['id']);
        $item_stmt->execute();
    }
    for ($i=1; $i <= $putters_num; $i++) {
        $item_stmt->bind_param("iii", $order_id, $putter_result['id'], $putter_result['id']);
        $item_stmt->execute();
    }
$item_stmt->close();
$conn->close();

header("Location: order_detail.php?id=" . $order_id);
exit();
?>