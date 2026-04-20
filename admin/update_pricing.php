<?php
require('auth_check.php');
require('../db.php');

$labor_cost    = (float) $_POST['labor_cost'];
$material_cost = (float) $_POST['material_cost'];

$stmt = $conn->prepare("UPDATE pricing_config SET labor_cost = ?, material_cost = ? WHERE id = 1");
$stmt->bind_param("dd", $labor_cost, $material_cost);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: pricing.php");
exit();
?>
