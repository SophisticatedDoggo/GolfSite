<?php
// DEPRECATED: This page is no longer in use.

    require('../db.php');

    // Compute min/max final price per brand + club type from real catalog data
    $results = $conn->query("
        SELECT
            g.brand,
            CASE WHEN LOWER(g.category) LIKE '%putter%' THEN 'putter' ELSE 'swinging' END AS club_type,
            MIN(ROUND(g.catalog_cost + pc.labor_cost + pc.material_cost + mt.markup, 2)) AS min_price,
            MAX(ROUND(g.catalog_cost + pc.labor_cost + pc.material_cost + mt.markup, 2)) AS max_price
        FROM grips g
        JOIN pricing_config pc ON pc.id = 1
        JOIN markup_tiers mt
            ON g.catalog_cost >= mt.min_price
           AND (mt.max_price IS NULL OR g.catalog_cost <= mt.max_price)
        WHERE g.active = 1
        GROUP BY g.brand, club_type
        ORDER BY g.brand, club_type
    ");
    $grip_prices_data = ($results && $results->num_rows > 0) ? $results->fetch_all(MYSQLI_ASSOC) : [];

    // Pull labor cost from config so JS doesn't hardcode it
    $config_result = $conn->query("SELECT labor_cost FROM pricing_config WHERE id = 1");
    $config = ($config_result && $config_result->num_rows > 0)
        ? $config_result->fetch_assoc()
        : ['labor_cost' => 5.00];

    // Build brand lists for each dropdown
    $swinging_brands = array_unique(array_column(
        array_filter($grip_prices_data, fn($r) => $r['club_type'] === 'swinging'),
        'brand'
    ));
    $putter_brands = array_unique(array_column(
        array_filter($grip_prices_data, fn($r) => $r['club_type'] === 'putter'),
        'brand'
    ));

    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Calculator | Smith's Golf Grips</title>
    <meta name="description" content="Estimate the cost of your golf club regripping order with Smith's Golf Grips. Choose from Golf Pride, Winn, and Super Stroke grips with $5 labor per grip.">
    <link rel="icon" type="image/svg+xml" href="../images/Smiths_Grips_Logo_alt.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <link rel="preload" href="../images/Pricing_Calculator_Background.webp" as="image">
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <header>
        <nav>
            <a href="#"><img src="../images/Smiths_Grips_Logo.svg" alt="Smith's Grips Logo"></a>
            <button class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul>
                <li><a href="../#home">HOME</a></li>
                <li><a href="../#services">SERVICES</a></li>
                <li><a href="../#pricing">PRICING</a></li>
                <li><a href="calculator.php">CALCULATOR</a></li>
                <li><a href="order.php">ORDER</a></li>
                <li><a href="../#about">ABOUT</a></li>
                <li><a href="../#contact">CONTACT</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="calculator">
            <div class="calculator-content">
                <h2>Price Calculator</h2>
                <p>Here you can enter the number of clubs/putters that you have
                    and the types of grips that you'd like to have added. Then we'll
                    be able to give you a ballpark estimate to the total cost of your order.
                </p>
                <p>Please note that results are shown as a price range — this reflects the variety
                    of grip options each brand offers, as individual grip prices can vary within a brand.
                </p>
                <p>Feel free to try it out below!</p>
            </div>
            <div class="calculator-card">
                <form action="" method="post">
                    <div>
                        <label for="clubs_num">Number of Swinging Clubs:</label>
                        <input type="number" value="0" min="0" max="99" name="clubs_num" id="clubs_num">
                    </div>
                    <div>
                        <label for="putters_num">Number of Putters:</label>
                        <input type="number" value="0" min="0" max="99" name="putters_num" id="putters_num">
                    </div>
                    <div>
                        <p>Providing Pre-Purchased Grips?</p>
                        <div class="radio-group">
                            <input type="radio" name="provide_grips" id="provide_grips_yes" value="yes">
                            <label for="provide_grips_yes">Yes</label>
                            <input type="radio" name="provide_grips" id="provide_grips_no" value="no">
                            <label for="provide_grips_no">No</label>
                        </div>
                    </div>
                    <div hidden class="club_brand_div">
                        <label for="club_grip_brand">Choose a Brand for Club Grips:</label>
                        <select name="club_grip_brand" id="club_grip_brand">
                            <?php foreach ($swinging_brands as $brand): ?>
                                <option value="<?php echo htmlspecialchars($brand); ?>"><?php echo htmlspecialchars($brand); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div hidden class="putter_brand_div">
                        <label for="putter_grip_brand">Choose a Brand for Putter Grips:</label>
                        <select name="putter_grip_brand" id="putter_grip_brand">
                            <?php foreach ($putter_brands as $brand): ?>
                                <option value="<?php echo htmlspecialchars($brand); ?>"><?php echo htmlspecialchars($brand); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="button" id="calc_button" onclick="">Calculate</button>
                    </div>
                    <div id="result"></div>
                </form>
            </div>
        </section>
    </main>
    <footer>
        <h3>Smith's Golf Grips</h3>
        <p>&copy; 2025 Smith's Golf Grips. All rights reserved.</p>
        <p><a href="tel:7247576563">724-757-6563</a></p>
        <p><a href="../admin/login.php">Admin</a></p>
    </footer>
    <script>
        const grip_prices = <?php echo json_encode($grip_prices_data); ?>;
        const labor_cost_per_grip = <?php echo json_encode((float)$config['labor_cost']); ?>;
    </script>
    <script src="../js/main.js"></script>
    <script src="../js/calculator.js"></script>
</body>
</html>