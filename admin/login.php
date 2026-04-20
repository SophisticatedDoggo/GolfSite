<?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require('../db.php');
        $user_name = trim($_POST['uname']);
        $password = trim($_POST['psw']);

        $stmt = $conn->prepare("SELECT password_hash
        FROM admin
        WHERE username = ?;");
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (isset($result) && password_verify($password, $result['password_hash'])) {
            $_SESSION["user"] = $user_name;
            header("Location: orders.php");
            exit();
        } else {
            $error_message = "Login Failed";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="icon" type="image/svg+xml" href="../images/Smiths_Grips_Logo_alt.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="login-page">
    <main>
        <section class="login">
            <div class="login-card">
                <div class="login-brand">
                    <h2>Smith's Golf Grips</h2>
                    <span>Admin Panel</span>
                </div>
                <form action="" method="POST" class="login-form">
                    <div>
                        <label for="uname">Username</label>
                        <input type="text" name="uname" id="uname" autocomplete="username">
                    </div>
                    <div>
                        <label for="psw">Password</label>
                        <input type="password" name="psw" id="psw" autocomplete="current-password">
                    </div>
                    <?php if (isset($error_message)): ?>
                        <p class="login-error"><?php echo htmlspecialchars($error_message) ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn-gold login-btn">Login</button>
                </form>
            </div>
        </section>
    </main>
    <footer>
        <h3>Smith's Golf Grips</h3>
        <p>&copy; 2025 Smith's Golf Grips. All rights reserved.</p>
        <p><a href="tel:7247576563">724-757-6563</a></p>
    </footer>
    <script src="../js/main.js"></script>
</body>
</html>
