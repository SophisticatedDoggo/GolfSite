<?php
    require('../db.php');

    $grip_id = (int) $_GET['grip_id'];

    $stmt = $conn->prepare("SELECT (g.catalog_cost + pc.labor_cost + pc.material_cost + mt.markup) AS final_price
        FROM grips g
        JOIN pricing_config pc ON pc.id = 1
        JOIN markup_tiers mt
        ON g.catalog_cost >= mt.min_price
        AND (mt.max_price IS NULL OR g.catalog_cost <= mt.max_price)
        WHERE g.id = ?;");
    $stmt->bind_param("i", $grip_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode(['price' => $row['final_price']]);
?>