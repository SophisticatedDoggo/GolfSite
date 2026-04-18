<?php
require('../db.php');


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
        <section class="catalog">
            <div class="catalog_text">
                <h1>Build Your Order</h1>
                <p>Browse our grip catalog below to find what you're looking for, then use the order form to make your selections and submit your request.</p>
            </div>
            <div class="catalog_card">
                <iframe src="../pdfjs/web/viewer.html?file=../../docs/grip_catalog.pdf#toolbar=0" frameborder="0"></iframe>
            </div>
        </section>
    </main>
</body>
</html>