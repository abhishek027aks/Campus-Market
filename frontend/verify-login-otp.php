<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP - Campus Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="form-container">
    <h2>Verify OTP</h2>

    <?php if(isset($_SESSION['otp_message'])){ ?>
        <p style="text-align:center;margin-bottom:15px;">
            <?php echo htmlspecialchars($_SESSION['otp_message']); ?>
        </p>
        <?php unset($_SESSION['otp_message']); ?>
    <?php } ?>

    <?php if(isset($_SESSION['dev_login_otp'])){ ?>
        <p style="text-align:center;margin-bottom:15px;">
            Development OTP:
            <strong><?php echo htmlspecialchars($_SESSION['dev_login_otp']); ?></strong>
        </p>
        <?php unset($_SESSION['dev_login_otp']); ?>
    <?php } ?>

    <form action="../backend/verify-login-otp.php" method="POST">
        <input type="text" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" pattern="[0-9]{6}" required>
        <button type="submit" name="verify_login_otp">Verify OTP</button>
    </form>

    <p style="text-align:center;margin-top:15px;">
        <a href="otp-login.php">Request new OTP</a>
    </p>
</div>

</body>
</html>
