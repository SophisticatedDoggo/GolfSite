<?php
require('auth_check.php');
require('../db.php');

$order_id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT o.id, o.customer_name, o.status, o.total_price, o.created_at, o.customer_email, o.customer_phone, o.notes, o.clubs_num, o.putters_num, o.own_grips
    FROM orders o
    WHERE o.id = ?;");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT oi.grip_id, oi.quantity, oi.unit_price, g.sku, g.brand, g.model, g.size, g.color, g.category
    FROM order_items oi
    JOIN grips g ON g.id = oi.grip_id
    WHERE oi.order_id = ?;");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_details = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$grip_results = $conn->query("
        SELECT id, brand, model, size, color, category FROM grips
        ORDER BY brand, model;
    ");

$grip_data = $grip_results->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Order Details</title>
    <link rel="icon" type="image/svg+xml" href="../images/Smiths_Grips_Logo_alt.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php require('sidebar.php'); ?>
    <main class="detail-main">
        <section class="detail-card">
            <h1>Order #<?php echo $order_id?></h1>
            <form action="update_order.php" method="POST" class="admin-form">
                <div>
                    <label for="cust_name">Name</label>
                    <input name="cust_name" id="cust_name" type="text" value="<?php echo htmlspecialchars($order['customer_name'])?>">
                </div>
                <div>
                    <label for="cust_email">Email</label>
                    <input name="cust_email" id="cust_email" type="email" value="<?php echo htmlspecialchars($order['customer_email'])?>">
                </div>
                <div>
                    <label for="cust_phone">Phone</label>
                    <input name="cust_phone" id="cust_phone" type="text" value="<?php echo htmlspecialchars($order['customer_phone'])?>">
                </div>
                <div>
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes"><?php echo htmlspecialchars($order['notes'])?></textarea>
                </div>
                <div>
                    <label for="clubs_num">Number of Swinging Clubs</label>
                    <input type="number" name="clubs_num" id="clubs_num" min="0" max="99" value="<?php echo (int)$order['clubs_num'] ?>">
                </div>
                <div>
                    <label for="putters_num">Number of Putters</label>
                    <input type="number" name="putters_num" id="putters_num" min="0" max="99" value="<?php echo (int)$order['putters_num'] ?>">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <p class="info-line">Created: <span><?php echo $order['created_at'] ?></span></p>
                <p class="info-line">Total Price: <span>$<?php echo number_format($order['total_price'], 2) ?></span></p>
                <input type="hidden" name="order_id" value="<?php echo $order['id'] ?>">
                <button type="submit" class="btn-gold">Save Changes</button>
            </form>
        </section>
        <section class="detail-card">
            <h1>Order Items <?php if ($order['own_grips'] == 1): ?>
                <span class="own-grips-badge">Own Grips</span>
            <?php endif; ?></h1>
            <?php
                $clubs   = array_values(array_filter($order_details, fn($i) => $i['category'] === 'swing'));
                $putters = array_values(array_filter($order_details, fn($i) => $i['category'] === 'putter'));
            ?>

            <?php if ($order['own_grips'] == 0): ?>
            <form action="update_order_items.php" method="POST" class="admin-form">
                <input type="hidden" name="order_id" value="<?php echo $order_id ?>">

                <datalist id="club_grip">
                    <?php foreach ($grip_data as $g): if ($g['category'] !== 'swing') continue;
                        $grip_label = $g['brand'] . ' - ' . $g['model'] . ' - ' . $g['size'] . ' - ' . $g['color'];
                    ?>
                        <option value="<?php echo htmlspecialchars($grip_label) ?>" data-id="<?php echo $g['id'] ?>"></option>
                    <?php endforeach; ?>
                </datalist>

                <datalist id="putter_grip">
                    <?php foreach ($grip_data as $g): if ($g['category'] !== 'putter') continue;
                        $grip_label = $g['brand'] . ' - ' . $g['model'] . ' - ' . $g['size'] . ' - ' . $g['color'];
                    ?>
                        <option value="<?php echo htmlspecialchars($grip_label) ?>" data-id="<?php echo $g['id'] ?>"></option>
                    <?php endforeach; ?>
                </datalist>

                <?php if ($order['clubs_num'] > 0): ?>
                <div class="club_div">
                    <p>Club Grips</p>
                    <div class="apply-all-div">
                        <label>Apply same grip to all clubs:</label>
                        <input type="text" list="club_grip" id="apply_all_clubs_input">
                        <button type="button" id="apply_all_clubs_btn">Apply to All</button>
                    </div>
                    <?php for ($n = 1; $n <= $order['clubs_num']; $n++):
                        $item = $clubs[$n - 1] ?? null;
                        $current_grip  = $item ? htmlspecialchars($item['brand'] . ' - ' . $item['model'] . ' - ' . $item['size'] . ' - ' . $item['color']) : '';
                        $current_id    = $item ? $item['grip_id'] : '';
                        $current_price = $item ? $item['unit_price'] : 0;
                        $current_sku   = $item ? htmlspecialchars($item['sku']) : '';
                    ?>
                        <div class="slot_div" data-price="<?php echo $current_price ?>">
                            <label>Club <?php echo $n ?>:</label>
                            <input type="text" list="club_grip" name="club_grip_<?php echo $n ?>" value="<?php echo $current_grip ?>">
                            <input type="hidden" name="club_grip_id_<?php echo $n ?>" value="<?php echo $current_id ?>">
                            <span class="slot_price">
                                <?php echo $current_price > 0 ? '$' . number_format($current_price, 2) : '' ?>
                                <?php if ($current_sku): ?><small class="slot_sku"><?php echo $current_sku ?></small><?php endif; ?>
                            </span>
                        </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <?php if ($order['putters_num'] > 0): ?>
                <div class="putter_div">
                    <p>Putter Grips</p>
                    <div class="apply-all-div">
                        <label>Apply same grip to all putters:</label>
                        <input type="text" list="putter_grip" id="apply_all_putters_input">
                        <button type="button" id="apply_all_putters_btn">Apply to All</button>
                    </div>
                    <?php for ($n = 1; $n <= $order['putters_num']; $n++):
                        $item = $putters[$n - 1] ?? null;
                        $current_grip  = $item ? htmlspecialchars($item['brand'] . ' - ' . $item['model'] . ' - ' . $item['size'] . ' - ' . $item['color']) : '';
                        $current_id    = $item ? $item['grip_id'] : '';
                        $current_price = $item ? $item['unit_price'] : 0;
                        $current_sku   = $item ? htmlspecialchars($item['sku']) : '';
                    ?>
                        <div class="slot_div" data-price="<?php echo $current_price ?>">
                            <label>Putter <?php echo $n ?>:</label>
                            <input type="text" list="putter_grip" name="putter_grip_<?php echo $n ?>" value="<?php echo $current_grip ?>">
                            <input type="hidden" name="putter_grip_id_<?php echo $n ?>" value="<?php echo $current_id ?>">
                            <span class="slot_price">
                                <?php echo $current_price > 0 ? '$' . number_format($current_price, 2) : '' ?>
                                <?php if ($current_sku): ?><small class="slot_sku"><?php echo $current_sku ?></small><?php endif; ?>
                            </span>
                        </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <input type="hidden" name="clubs_num" value="<?php echo $order['clubs_num'] ?>">
                <input type="hidden" name="putters_num" value="<?php echo $order['putters_num'] ?>">
                <button type="submit" class="btn-gold">Save Item Changes</button>
            </form>
            <form action="clear_order_items.php" method="POST" style="margin-top:12px;">
                <input type="hidden" name="order_id" value="<?php echo $order_id ?>">
                <button type="submit" class="btn-danger">Switch to Own Grips</button>
            </form>

            <?php else: ?>
            <div class="own-grips-notice">
                <span class="own-grips-icon">&#9935;</span>
                <div>
                    <strong>Customer is providing their own grips</strong>
                    <p>No grip selection required. Only labor &amp; materials will be charged.</p>
                </div>
            </div>
            <form action="toggle_grips.php" method="POST" class="admin-form">
                <input type="hidden" name="order_id" value="<?php echo $order_id ?>">
                <div>
                    <label for="toggle_clubs_num">Number of Swinging Clubs</label>
                    <input type="number" name="clubs_num" id="toggle_clubs_num" min="0" max="99" value="<?php echo (int)$order['clubs_num'] ?>">
                </div>
                <div>
                    <label for="toggle_putters_num">Number of Putters</label>
                    <input type="number" name="putters_num" id="toggle_putters_num" min="0" max="99" value="<?php echo (int)$order['putters_num'] ?>">
                </div>
                <button type="submit" class="btn-gold">Switch to Grip Selection</button>
            </form>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <h3>Smith's Golf Grips</h3>
        <p>&copy; 2025 Smith's Golf Grips. All rights reserved.</p>
        <p><a href="tel:7247576563">724-757-6563</a></p>
    </footer>
    <script src="../js/main.js"></script>
    <script src="../js/order_details.js"></script>
</body>
</html>