<?php
    require('../db.php');

    $results = $conn->query("SELECT * FROM grip_prices");
    $results_json = json_encode($results->fetch_all(MYSQLI_ASSOC));
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
                            <option value="Golf Pride">Golf Pride</option>
                            <option value="Winn">Winn</option>
                            <option value="Super Stroke">Super Stroke</option>
                        </select>
                    </div>
                    <div hidden class="putter_brand_div">
                        <label for="putter_grip_brand">Choose a Brand for Putter Grips:</label>
                        <select name="putter_grip_brand" id="putter_grip_brand">
                            <option value="Golf Pride">Golf Pride</option>
                            <option value="Winn">Winn</option>
                            <option value="Super Stroke">Super Stroke</option>
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
    </footer>
    <script>const grip_prices = <?php echo $results_json?>;</script>
    <script src="../js/main.js"></script>
    <script src="../js/calculator.js"></script>
</body>
</html>