<?php
require('auth_check.php');
require('../db.php');

$tier_id   = (int)   $_POST['tier_id'];
$label     = trim($_POST['label']);
$min_price = (float) $_POST['min_price'];
$max_price = $_POST['max_price'] !== '' ? (float) $_POST['max_price'] : null;
$markup    = (float) $_POST['markup'];

$stmt = $conn->prepare("UPDATE markup_tiers SET min_price = ?, max_price = ?, markup = ?, label = ? WHERE id = ?");
$stmt->bind_param("dddsi", $min_price, $max_price, $markup, $label, $tier_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: pricing.php");
exit();
?>
