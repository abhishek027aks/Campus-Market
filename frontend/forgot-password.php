<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - Campus Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="form-container">
    <h2>Forgot Password</h2>

    <?php if(isset($_SESSION['reset_message'])){ ?>
        <p style="text-align:center;margin-bottom:15px;">
            <?php echo htmlspecialchars($_SESSION['reset_message']); ?>
        </p>
        <?php unset($_SESSION['reset_message']); ?>
    <?php } ?>

    <?php if(isset($_SESSION['dev_reset_link'])){ ?>
        <p style="text-align:center;margin-bottom:15px;">
            Development reset link:
            <a href="<?php echo htmlspecialchars($_SESSION['dev_reset_link']); ?>">Reset Password</a>
        </p>
        <?php unset($_SESSION['dev_reset_link']); ?>
    <?php } ?>

    <form action="../backend/request-password-reset.php" method="POST">
        <input type="email" name="email" placeholder="Registered Email" required>
        <button type="submit" name="request_reset">Send Reset Link</button>
    </form>

    <p style="text-align:center;margin-top:15px;">
        Remembered password?
        <a href="login.html">Login</a>
    </p>
</div>

</body>
</html>
