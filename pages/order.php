<?php
require('../db.php');

$grip_results = $conn->query("
    SELECT id, brand, model, size, color, category
    FROM grips
    ORDER BY brand, model
");

$grip_data = $grip_results->fetch_all(MYSQLI_ASSOC);

$swing_grips = array_values(array_filter($grip_data, fn($g) => $g['category'] === 'swing'));
$putter_grips = array_values(array_filter($grip_data, fn($g) => $g['category'] === 'putter'));

$config_result = $conn->query("SELECT labor_cost, material_cost FROM pricing_config WHERE id = 1");
$config = $config_result->fetch_assoc();
$labor_per_club = $config['labor_cost'] + $config['material_cost'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order</title>
    <link rel="icon" type="image/svg+xml" href="../images/Smiths_Grips_Logo_alt.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
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
                <li><a href="../index.html#home">HOME</a></li>
                <li><a href="../index.html#services">SERVICES</a></li>
                <li><a href="../index.html#pricing">PRICING</a></li>
                <li><a href="../index.html#about">ABOUT</a></li>
                <li><a href="../index.html#contact">CONTACT</a></li>
                <li><a href="order.php">ORDER</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="order_page">
            <div class="order_header">
                <h1>Build Your Order</h1>
                <p>Use the catalog below to look through the grip options currently available. After that, fill out the order form with the number of clubs you need done, 
                    choose your grips if needed, and send us your information so we can follow up and confirm the details of your order.
                </p>
            </div>

            <div class="order_columns">
                <div class="catalog_side">
                    <div class="catalog_card">
                        <iframe class="catalog_frame" src="../pdfjs/web/viewer.html?file=../../docs/grip_catalog.pdf#toolbar=0" frameborder="0"></iframe>
                        <a href="../docs/grip_catalog.pdf">
                            <img class="catalog_screenshot" src="../images/catalog_screenshot.webp" alt="catalog screenshot">
                            <b class="catalog_link_text">Click to Open Catalog</b>
                        </a>
                    </div>
                </div>

                <div class="form_side">
                    <div class="form_card">
                        <form action="submit_order.php" method="POST">
                            <input type="hidden" id="labor_per_club" value="<?php echo htmlspecialchars($labor_per_club); ?>">

                            <div>
                                <label for="clubs_num">Number of Swinging Clubs:</label>
                                <input type="number" value="0" min="0" max="99" name="clubs_num" id="clubs_num" required>
                            </div>

                            <div>
                                <label for="putters_num">Number of Putters:</label>
                                <input type="number" value="0" min="0" max="99" name="putters_num" id="putters_num" required>
                            </div>

                            <div>
                                <p>Are you providing your own grips?</p>
                                <div class="radio-group">
                                    <input type="radio" name="own_grips" id="own_grips_yes" value="1" required>
                                    <label for="own_grips_yes">Yes</label>

                                    <input type="radio" name="own_grips" id="own_grips_no" value="0">
                                    <label for="own_grips_no">No</label>
                                </div>
                            </div>

                            <div hidden class="club_div"></div>
                            <div hidden class="putter_div"></div>

                            <div>
                                <label for="cust_name">Name:</label>
                                <input type="text" name="cust_name" id="cust_name" required>
                            </div>

                            <div>
                                <label for="cust_email">Email:</label>
                                <input type="email" name="cust_email" id="cust_email" required>
                            </div>

                            <div>
                                <label for="cust_phone">Phone:</label>
                                <input type="tel" name="cust_phone" id="cust_phone" required>
                            </div>

                            <div>
                                <label for="cust_notes">Additional Notes:</label>
                                <textarea name="cust_notes" id="cust_notes"></textarea>
                            </div>

                            <div id="order_total"></div>
                            <div class="h-captcha" data-sitekey="611ee0d8-1f5e-4436-99a6-51720f0827fe" data-theme="dark"></div>
                            <button type="submit">Submit Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <h3>Smith's Golf Grips</h3>
        <p>&copy; 2025 Smith's Golf Grips. All rights reserved.</p>
        <p><a href="tel:7247576563">724-757-6563</a></p>
        <p><a href="../admin/login.php">Admin</a></p>
    </footer>

    <div id="toast" hidden>
        <p id="toast-message"></p>
    </div>

    <script>
        const swingGripOptions = <?php echo json_encode(array_map(function($g) {
            return [
                'id' => (int)$g['id'],
                'label' => $g['brand'] . ' - ' . $g['model'] . ' - ' . $g['size'] . ' - ' . $g['color']
            ];
        }, $swing_grips), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const putterGripOptions = <?php echo json_encode(array_map(function($g) {
            return [
                'id' => (int)$g['id'],
                'label' => $g['brand'] . ' - ' . $g['model'] . ' - ' . $g['size'] . ' - ' . $g['color']
            ];
        }, $putter_grips), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <script src="../js/main.js"></script>
    <script src="../js/order.js"></script>
</body>
</html>