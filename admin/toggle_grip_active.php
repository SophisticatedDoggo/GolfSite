<?php
require('auth_check.php');
require('../db.php');

$grip_id    = (int) $_POST['grip_id'];
$new_active = (int) $_POST['active'] === 1 ? 0 : 1;

$stmt = $conn->prepare("UPDATE grips SET active = ? WHERE id = ?");
$stmt->bind_param("ii", $new_active, $grip_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: grips.php");
exit();
?>
