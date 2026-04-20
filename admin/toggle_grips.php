<?php
require('auth_check.php');
require('../db.php');

$order_id = (int) ($_POST['order_id'] ?? 0);
$clubs_num = max(0, (int) ($_POST['clubs_num'] ?? 0));
$putters_num = max(0, (int) ($_POST['putters_num'] ?? 0));

if ($order_id <= 0) {
    header("Location: orders.php");
    exit();
}

// Get one default swing grip
$swing_grip_result = $conn->query("
    SELECT id
    FROM grips
    WHERE category = 'swing'
    ORDER BY id
    LIMIT 1
");
$club_result = $swing_grip_result ? $swing_grip_result->fetch_assoc() : null;

// Get one default putter grip
$putter_grip_result = $conn->query("
    SELECT id
    FROM grips
    WHERE category = 'putter'
    ORDER BY id
    LIMIT 1
");
$putter_result = $putter_grip_result ? $putter_grip_result->fetch_assoc() : null;

// 1. Update the order counts and switch off own-grips mode
$update_order_stmt = $conn->prepare("
    UPDATE orders
    SET clubs_num = ?, putters_num = ?, own_grips = 0
    WHERE id = ?
");
$update_order_stmt->bind_param("iii", $clubs_num, $putters_num, $order_id);
$update_order_stmt->execute();
$update_order_stmt->close();

// 2. Clear any old order items so duplicates do not stack
$delete_stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
$delete_stmt->bind_param("i", $order_id);
$delete_stmt->execute();
$delete_stmt->close();

// 3. Insert placeholder/default grip rows for each club
$item_stmt = $conn->prepare("
    INSERT INTO order_items (order_id, grip_id, quantity, unit_price)
    SELECT ?, ?, 1, (g.catalog_cost + mt.markup + pc.labor_cost + pc.material_cost)
    FROM grips g
    JOIN pricing_config pc ON pc.id = 1
    JOIN markup_tiers mt
        ON g.catalog_cost >= mt.min_price
       AND (mt.max_price IS NULL OR g.catalog_cost <= mt.max_price)
    WHERE g.id = ?
");

if ($club_result && $clubs_num > 0) {
    for ($i = 1; $i <= $clubs_num; $i++) {
        $grip_id = (int) $club_result['id'];
        $item_stmt->bind_param("iii", $order_id, $grip_id, $grip_id);
        $item_stmt->execute();
    }
}

if ($putter_result && $putters_num > 0) {
    for ($i = 1; $i <= $putters_num; $i++) {
        $grip_id = (int) $putter_result['id'];
        $item_stmt->bind_param("iii", $order_id, $grip_id, $grip_id);
        $item_stmt->execute();
    }
}

$item_stmt->close();

// 4. Recalculate total price
$total_result = $conn->query("
    SELECT COALESCE(SUM(quantity * unit_price), 0) AS total
    FROM order_items
    WHERE order_id = $order_id
");
$total_row = $total_result->fetch_assoc();
$total = (float) $total_row['total'];

$total_stmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
$total_stmt->bind_param("di", $total, $order_id);
$total_stmt->execute();
$total_stmt->close();

$conn->close();

header("Location: order_detail.php?id=" . $order_id);
exit();
?>