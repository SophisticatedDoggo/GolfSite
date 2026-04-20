<?php
require('auth_check.php');
require('../db.php');

$grip_id      = (int)   $_POST['grip_id'];
$brand        = trim($_POST['brand']);
$sku          = trim($_POST['sku']);
$model        = trim($_POST['model']);
$size         = trim($_POST['size']);
$color        = trim($_POST['color']);
$core         = trim($_POST['core']);
$catalog_cost = (float) $_POST['catalog_cost'];
$category     = trim($_POST['category']);
$image_path   = trim($_POST['image_path']);

$stmt = $conn->prepare("UPDATE grips
                        SET brand = ?, sku = ?, model = ?, size = ?, color = ?, core = ?,
                            catalog_cost = ?, category = ?, image_path = ?
                        WHERE id = ?");
$stmt->bind_param("ssssssdssi", $brand, $sku, $model, $size, $color, $core, $catalog_cost, $category, $image_path, $grip_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: grips.php");
exit();
?>
