<?php
session_start();

$token = isset($_GET['token']) ? $_GET['token'] : "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Campus Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="form-container">
    <h2>Reset Password</h2>

    <?php if(isset($_SESSION['reset_error'])){ ?>
        <p style="text-align:center;margin-bottom:15px;color:#dc3545;">
            <?php echo htmlspecialchars($_SESSION['reset_error']); ?>
        </p>
        <?php unset($_SESSION['reset_error']); ?>
    <?php } ?>

    <form action="../backend/reset-password.php" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit" name="reset_password">Update Password</button>
    </form>
</div>

</body>
</html>
