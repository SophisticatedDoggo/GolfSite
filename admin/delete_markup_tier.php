<?php
require('auth_check.php');
require('../db.php');

$tier_id = (int) $_POST['tier_id'];

$stmt = $conn->prepare("DELETE FROM markup_tiers WHERE id = ?");
$stmt->bind_param("i", $tier_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: pricing.php");
exit();
?>
