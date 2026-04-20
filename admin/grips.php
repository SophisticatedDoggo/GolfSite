<?php
require('auth_check.php');
require('../db.php');

$edit_grip = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM grips WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_grip = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$result = $conn->query("SELECT * FROM grips ORDER BY brand, model, size");
$grips = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Grips</title>
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

        <?php if ($edit_grip): ?>
        <section class="admin-section-card" style="max-width:700px;margin-bottom:28px;">
            <h1>Edit Grip #<?php echo $edit_grip['id'] ?></h1>
            <form action="update_grip.php" method="POST" class="admin-form">
                <input type="hidden" name="grip_id" value="<?php echo $edit_grip['id'] ?>">
                <div>
                    <label>Brand</label>
                    <input type="text" name="brand" value="<?php echo htmlspecialchars($edit_grip['brand']) ?>" required>
                </div>
                <div>
                    <label>SKU</label>
                    <input type="text" name="sku" value="<?php echo htmlspecialchars($edit_grip['sku']) ?>" required>
                </div>
                <div>
                    <label>Model</label>
                    <input type="text" name="model" value="<?php echo htmlspecialchars($edit_grip['model']) ?>" required>
                </div>
                <div>
                    <label>Size</label>
                    <input type="text" name="size" value="<?php echo htmlspecialchars($edit_grip['size']) ?>" required>
                </div>
                <div>
                    <label>Color</label>
                    <input type="text" name="color" value="<?php echo htmlspecialchars($edit_grip['color']) ?>" required>
                </div>
                <div>
                    <label>Core</label>
                    <input type="text" name="core" value="<?php echo htmlspecialchars($edit_grip['core']) ?>">
                </div>
                <div>
                    <label>Catalog Cost ($)</label>
                    <input type="number" name="catalog_cost" step="0.01" min="0" value="<?php echo $edit_grip['catalog_cost'] ?>" required>
                </div>
                <div>
                    <label>Category</label>
                    <select name="category">
                        <option value="swing"  <?php echo $edit_grip['category'] === 'swing'  ? 'selected' : '' ?>>Swing</option>
                        <option value="putter" <?php echo $edit_grip['category'] === 'putter' ? 'selected' : '' ?>>Putter</option>
                    </select>
                </div>
                <div>
                    <label>Image Path</label>
                    <input type="text" name="image_path" value="<?php echo htmlspecialchars($edit_grip['image_path']) ?>">
                </div>
                <div style="display:flex;gap:12px;margin-top:8px;">
                    <button type="submit" class="btn-gold">Save Changes</button>
                    <a href="grips.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <div class="grips-header">
            <h1 style="margin:0;">Grips</h1>
            <button class="btn-gold" id="toggle-add-btn">+ Add Grip</button>
        </div>

        <section class="admin-section-card" id="add-grip-section" style="max-width:700px;margin-bottom:28px;display:none;">
            <h1>Add New Grip</h1>
            <form action="add_grip.php" method="POST" class="admin-form">
                <div>
                    <label>Brand</label>
                    <input type="text" name="brand" required>
                </div>
                <div>
                    <label>SKU</label>
                    <input type="text" name="sku" required>
                </div>
                <div>
                    <label>Model</label>
                    <input type="text" name="model" required>
                </div>
                <div>
                    <label>Size</label>
                    <input type="text" name="size" required>
                </div>
                <div>
                    <label>Color</label>
                    <input type="text" name="color" required>
                </div>
                <div>
                    <label>Core</label>
                    <input type="text" name="core">
                </div>
                <div>
                    <label>Catalog Cost ($)</label>
                    <input type="number" name="catalog_cost" step="0.01" min="0" required>
                </div>
                <div>
                    <label>Category</label>
                    <select name="category">
                        <option value="swing">Swing</option>
                        <option value="putter">Putter</option>
                    </select>
                </div>
                <div>
                    <label>Image Path</label>
                    <input type="text" name="image_path">
                </div>
                <button type="submit" class="btn-gold" style="margin-top:8px;">Add Grip</button>
            </form>
        </section>

        <div class="admin-table-wrap">
            <table id="grips-table">
                <thead>
                    <tr>
                        <th class="sortable" data-col="0">Brand <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="1">SKU <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="2">Model <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="3">Size <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="4">Color <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="5">Core <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="6">Cost <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="7">Category <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="8">Status <span class="sort-icon">↕</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grips)): ?>
                        <tr><td colspan="10" style="text-align:center;color:#888;">No grips found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($grips as $grip): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grip['brand']) ?></td>
                        <td><?php echo htmlspecialchars($grip['sku']) ?></td>
                        <td><?php echo htmlspecialchars($grip['model']) ?></td>
                        <td><?php echo htmlspecialchars($grip['size']) ?></td>
                        <td><?php echo htmlspecialchars($grip['color']) ?></td>
                        <td><?php echo htmlspecialchars($grip['core']) ?></td>
                        <td>$<?php echo number_format($grip['catalog_cost'], 2) ?></td>
                        <td><?php echo ucfirst($grip['category']) ?></td>
                        <td>
                            <span class="status-badge <?php echo $grip['active'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?php echo $grip['active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <a href="grips.php?edit=<?php echo $grip['id'] ?>">Edit</a>
                            <form action="toggle_grip_active.php" method="POST" class="delete-form">
                                <input type="hidden" name="grip_id" value="<?php echo $grip['id'] ?>">
                                <input type="hidden" name="active" value="<?php echo $grip['active'] ?>">
                                <button type="submit" class="<?php echo $grip['active'] ? 'btn-delete' : 'btn-activate' ?>">
                                    <?php echo $grip['active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
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
            const section = document.getElementById('add-grip-section');
            const visible = section.style.display !== 'none';
            section.style.display = visible ? 'none' : 'block';
            this.textContent = visible ? '+ Add Grip' : '✕ Cancel';
        });

        (function () {
            const table = document.getElementById('grips-table');
            const tbody = table.querySelector('tbody');
            let sortCol = -1, sortAsc = true;

            table.querySelectorAll('th.sortable').forEach(th => {
                th.addEventListener('click', () => {
                    const col = parseInt(th.dataset.col);
                    sortAsc = sortCol === col ? !sortAsc : true;
                    sortCol = col;

                    table.querySelectorAll('th.sortable').forEach(h => {
                        h.querySelector('.sort-icon').textContent = '↕';
                        h.classList.remove('sort-asc', 'sort-desc');
                    });
                    th.querySelector('.sort-icon').textContent = sortAsc ? '↑' : '↓';
                    th.classList.add(sortAsc ? 'sort-asc' : 'sort-desc');

                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    rows.sort((a, b) => {
                        const aText = a.cells[col]?.textContent.trim() ?? '';
                        const bText = b.cells[col]?.textContent.trim() ?? '';
                        const aNum = parseFloat(aText.replace(/[^0-9.-]/g, ''));
                        const bNum = parseFloat(bText.replace(/[^0-9.-]/g, ''));
                        let cmp = (!isNaN(aNum) && !isNaN(bNum)) ? aNum - bNum : aText.localeCompare(bText);
                        return sortAsc ? cmp : -cmp;
                    });
                    rows.forEach(r => tbody.appendChild(r));
                });
            });
        })();
    </script>
</body>
</html>
