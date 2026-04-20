<?php
require('auth_check.php');
require('../db.php');

$result = $conn->query(
    'SELECT
     o.id,
     o.customer_name,
     o.status,
     o.total_price,
     o.created_at,
     o.customer_email,
     o.customer_phone,
     o.notes,
     o.clubs_num,
     o.putters_num
    FROM orders o');
$orders = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders</title>
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
        <div class="admin-table-wrap">
            <h1>Orders</h1>
            <table id="orders-table">
                <thead>
                    <tr>
                        <th class="sortable" data-col="0">Name <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="1">Email <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="2">Phone <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="3">Clubs <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="4">Putters <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="5">Notes <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="6">Status <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="7">Total Price <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="8">Created <span class="sort-icon">↕</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="10">No Orders Yet</td></tr>
                    <?php endif; ?>
                    <?php foreach ($orders as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['customer_name'])?></td>
                            <td><?php echo htmlspecialchars($row['customer_email'])?></td>
                            <td><?php echo htmlspecialchars($row['customer_phone'])?></td>
                            <td><?php echo $row['clubs_num']?></td>
                            <td><?php echo $row['putters_num']?></td>
                            <td><?php echo htmlspecialchars($row['notes'])?></td>
                            <td><?php echo htmlspecialchars($row['status'])?></td>
                            <td>$<?php echo number_format($row['total_price'], 2)?></td>
                            <td><?php echo date('M j, Y', strtotime($row['created_at']))?></td>
                            <td class="actions-cell">
                                <a href="order_detail.php?id=<?php echo $row['id'] ?>">View/Edit</a>
                                <form action="delete_order.php" method="POST" class="delete-form" onsubmit="return confirm('Delete this order? This cannot be undone.');">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id'] ?>">
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
        (function () {
            const table = document.getElementById('orders-table');
            const tbody = table.querySelector('tbody');
            let sortCol = -1, sortAsc = true;

            table.querySelectorAll('th.sortable').forEach(th => {
                th.addEventListener('click', () => {
                    const col = parseInt(th.dataset.col);
                    if (sortCol === col) {
                        sortAsc = !sortAsc;
                    } else {
                        sortCol = col;
                        sortAsc = true;
                    }

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
                        let cmp;
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            cmp = aNum - bNum;
                        } else {
                            cmp = aText.localeCompare(bText);
                        }
                        return sortAsc ? cmp : -cmp;
                    });
                    rows.forEach(r => tbody.appendChild(r));
                });
            });
        })();
    </script>
</body>
</html>