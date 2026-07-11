<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification - Campus Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="form-container">
    <h2>Email Verification</h2>

    <p style="text-align:center;margin-bottom:15px;">
        <?php
        echo isset($_SESSION['verify_message'])
            ? htmlspecialchars($_SESSION['verify_message'])
            : "Please verify your email before login.";
        unset($_SESSION['verify_message']);
        ?>
    </p>

    <?php if(isset($_SESSION['dev_verify_link'])){ ?>
        <p style="text-align:center;margin-bottom:15px;">
            Development verification link:
            <a href="<?php echo htmlspecialchars($_SESSION['dev_verify_link']); ?>">Verify Email</a>
        </p>
        <?php unset($_SESSION['dev_verify_link']); ?>
    <?php } ?>

    <p style="text-align:center;">
        <a href="login.html">Back To Login</a>
    </p>
</div>

</body>
</html>
