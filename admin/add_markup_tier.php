<?php
require('auth_check.php');
require('../db.php');

$label     = trim($_POST['label']);
$min_price = (float) $_POST['min_price'];
$max_price = $_POST['max_price'] !== '' ? (float) $_POST['max_price'] : null;
$markup    = (float) $_POST['markup'];

$stmt = $conn->prepare("INSERT INTO markup_tiers (min_price, max_price, markup, label) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ddds", $min_price, $max_price, $markup, $label);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: pricing.php");
exit();
?>
