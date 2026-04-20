<?php
require('auth_check.php');
require('../db.php');

$edit_tier = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM markup_tiers WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_tier = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$pricing = $conn->query("SELECT * FROM pricing_config WHERE id = 1")->fetch_assoc();
$tiers   = $conn->query("SELECT * FROM markup_tiers ORDER BY min_price")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pricing</title>
    <link rel="icon" type="image/svg+xml" href="../images/Smiths_Grips_Logo_alt.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php require('sidebar.php'); ?>
    <main>

        <section class="admin-section-card" style="max-width:480px;margin-bottom:28px;">
            <h1>Labor &amp; Materials</h1>
            <form action="update_pricing.php" method="POST" class="admin-form">
                <div>
                    <label>Labor Cost per Grip ($)</label>
                    <input type="number" name="labor_cost" step="0.01" min="0" value="<?php echo $pricing['labor_cost'] ?>" required>
                </div>
                <div>
                    <label>Material Cost per Grip ($)</label>
                    <input type="number" name="material_cost" step="0.01" min="0" value="<?php echo $pricing['material_cost'] ?>" required>
                </div>
                <p class="info-line">Last updated: <span><?php echo $pricing['updated_at'] ?></span></p>
                <button type="submit" class="btn-gold" style="margin-top:8px;">Save</button>
            </form>
        </section>

        <?php if ($edit_tier): ?>
        <section class="admin-section-card" style="max-width:480px;margin-bottom:28px;">
            <h1>Edit Markup Tier</h1>
            <form action="update_markup_tier.php" method="POST" class="admin-form">
                <input type="hidden" name="tier_id" value="<?php echo $edit_tier['id'] ?>">
                <div>
                    <label>Label</label>
                    <input type="text" name="label" value="<?php echo htmlspecialchars($edit_tier['label']) ?>" required>
                </div>
                <div>
                    <label>Min Catalog Cost ($)</label>
                    <input type="number" name="min_price" step="0.01" min="0" value="<?php echo $edit_tier['min_price'] ?>" required>
                </div>
                <div>
                    <label>Max Catalog Cost ($) <span style="opacity:.5;font-weight:400;">(leave blank = no limit)</span></label>
                    <input type="number" name="max_price" step="0.01" min="0" value="<?php echo $edit_tier['max_price'] ?? '' ?>">
                </div>
                <div>
                    <label>Markup ($)</label>
                    <input type="number" name="markup" step="0.01" min="0" value="<?php echo $edit_tier['markup'] ?>" required>
                </div>
                <div style="display:flex;gap:12px;margin-top:8px;">
                    <button type="submit" class="btn-gold">Save Changes</button>
                    <a href="pricing.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <div class="grips-header">
            <h1 style="margin:0;">Markup Tiers</h1>
            <button class="btn-gold" id="toggle-add-btn">+ Add Tier</button>
        </div>

        <section class="admin-section-card" id="add-tier-section" style="max-width:480px;margin-bottom:28px;display:none;">
            <h1>Add Markup Tier</h1>
            <form action="add_markup_tier.php" method="POST" class="admin-form">
                <div>
                    <label>Label</label>
                    <input type="text" name="label" placeholder="e.g. Under $10" required>
                </div>
                <div>
                    <label>Min Catalog Cost ($)</label>
                    <input type="number" name="min_price" step="0.01" min="0" required>
                </div>
                <div>
                    <label>Max Catalog Cost ($) <span style="opacity:.5;font-weight:400;">(leave blank = no limit)</span></label>
                    <input type="number" name="max_price" step="0.01" min="0">
                </div>
                <div>
                    <label>Markup ($)</label>
                    <input type="number" name="markup" step="0.01" min="0" required>
                </div>
                <button type="submit" class="btn-gold" style="margin-top:8px;">Add Tier</button>
            </form>
        </section>

        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Min Cost</th>
                        <th>Max Cost</th>
                        <th>Markup</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tiers)): ?>
                        <tr><td colspan="5" style="text-align:center;color:#888;">No tiers defined.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($tiers as $tier): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tier['label']) ?></td>
                        <td>$<?php echo number_format($tier['min_price'], 2) ?></td>
                        <td><?php echo $tier['max_price'] !== null ? '$' . number_format($tier['max_price'], 2) : '<span style="color:#aaa;">No limit</span>' ?></td>
                        <td>$<?php echo number_format($tier['markup'], 2) ?></td>
                        <td class="actions-cell">
                            <a href="pricing.php?edit=<?php echo $tier['id'] ?>">Edit</a>
                            <form action="delete_markup_tier.php" method="POST" class="delete-form"
                                  onsubmit="return confirm('Delete this tier?');">
                                <input type="hidden" name="tier_id" value="<?php echo $tier['id'] ?>">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <footer>
        <h3>Smith's Golf Grips</h3>
        <p>&copy; 2025 Smith's Golf Grips. All rights reserved.</p>
        <p><a href="tel:7247576563">724-757-6563</a></p>
    </footer>
    <script src="../js/main.js"></script>
    <script>
        document.getElementById('toggle-add-btn').addEventListener('click', function () {
            const section = document.getElementById('add-tier-section');
            const visible = section.style.display !== 'none';
            section.style.display = visible ? 'none' : 'block';
            this.textContent = visible ? '+ Add Tier' : '✕ Cancel';
        });
    </script>
</body>
</html>
