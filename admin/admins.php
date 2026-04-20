<?php
require('auth_check.php');
require('../db.php');

$current_user = $_SESSION['user'];

$reset_admin = null;
if (isset($_GET['reset'])) {
    $reset_id = (int) $_GET['reset'];
    $stmt = $conn->prepare("SELECT id, username FROM admin WHERE id = ?");
    $stmt->bind_param("i", $reset_id);
    $stmt->execute();
    $reset_admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$admins = $conn->query("SELECT id, username FROM admin ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Admins</title>
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

        <?php if ($reset_admin): ?>
        <section class="admin-section-card" style="max-width:440px;margin-bottom:28px;">
            <h1>Reset Password</h1>
            <p style="color:rgba(255,255,255,0.6);margin-bottom:20px;font-size:14px;">
                Setting new password for <strong style="color:white;"><?php echo htmlspecialchars($reset_admin['username']) ?></strong>
            </p>
            <form action="reset_admin_password.php" method="POST" class="admin-form">
                <input type="hidden" name="admin_id" value="<?php echo $reset_admin['id'] ?>">
                <div>
                    <label>New Password</label>
                    <input type="password" name="new_password" required autocomplete="new-password">
                </div>
                <div>
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required autocomplete="new-password">
                </div>
                <div style="display:flex;gap:12px;margin-top:8px;">
                    <button type="submit" class="btn-gold">Reset Password</button>
                    <a href="admins.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'mismatch'): ?>
            <div class="error-banner">Passwords do not match. Please try again.</div>
        <?php endif; ?>

        <div class="grips-header">
            <h1 style="margin:0;">Admin Accounts</h1>
            <button class="btn-gold" id="toggle-add-btn">+ Add Admin</button>
        </div>

        <section class="admin-section-card" id="add-admin-section" style="max-width:440px;margin-bottom:28px;display:none;">
            <h1>Add Admin Account</h1>
            <form action="add_admin.php" method="POST" class="admin-form">
                <div>
                    <label>Username</label>
                    <input type="text" name="username" required autocomplete="off">
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="new-password">
                </div>
                <div>
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-gold" style="margin-top:8px;">Create Admin</button>
            </form>
        </section>

        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)): ?>
                        <tr><td colspan="3" style="text-align:center;color:#888;">No admins found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo $admin['id'] ?></td>
                        <td>
                            <?php echo htmlspecialchars($admin['username']) ?>
                            <?php if ($admin['username'] === $current_user): ?>
                                <span class="you-badge">You</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <a href="admins.php?reset=<?php echo $admin['id'] ?>">Reset Password</a>
                            <?php if ($admin['username'] !== $current_user): ?>
                            <form action="delete_admin.php" method="POST" class="delete-form"
                                  onsubmit="return confirm('Delete admin \'<?php echo htmlspecialchars($admin['username']) ?>\'?');">
                                <input type="hidden" name="admin_id" value="<?php echo $admin['id'] ?>">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                            <?php endif; ?>
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
            const section = document.getElementById('add-admin-section');
            const visible = section.style.display !== 'none';
            section.style.display = visible ? 'none' : 'block';
            this.textContent = visible ? '+ Add Admin' : '✕ Cancel';
        });
    </script>
</body>
</html>
