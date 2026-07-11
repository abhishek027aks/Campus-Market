<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Login - Campus Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="form-container">
    <h2>OTP Login</h2>

    <?php if(isset($_SESSION['otp_message'])){ ?>
        <p style="text-align:center;margin-bottom:15px;">
            <?php echo htmlspecialchars($_SESSION['otp_message']); ?>
        </p>
        <?php unset($_SESSION['otp_message']); ?>
    <?php } ?>

    <form action="../backend/request-login-otp.php" method="POST">
        <input type="email" name="email" placeholder="Registered Email" required>
        <button type="submit" name="request_login_otp">Send OTP</button>
    </form>

    <p style="text-align:center;margin-top:15px;">
        Prefer password?
        <a href="login.html">Login with password</a>
    </p>
</div>

</body>
</html>
