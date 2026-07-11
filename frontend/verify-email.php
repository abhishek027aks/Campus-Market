<?php
session_start();
include "../backend/config.php";

$token = isset($_GET['token']) ? trim($_GET['token']) : "";
$message = "Verification link is invalid or expired.";

if($token !== ""){
    $token_hash = mysqli_real_escape_string($conn, hash("sha256", $token));

    $result = mysqli_query(
        $conn,
        "SELECT * FROM email_verifications
         WHERE token_hash='$token_hash'
         AND used_at IS NULL
         AND expires_at > NOW()
         ORDER BY id DESC
         LIMIT 1"
    );
    $verification = $result ? mysqli_fetch_assoc($result) : null;

    if($verification){
        $verification_id = (int)$verification['id'];
        $user_id = (int)$verification['user_id'];

        mysqli_query($conn, "UPDATE users SET email_verified=1 WHERE id='$user_id'");
        mysqli_query($conn, "UPDATE email_verifications SET used_at=NOW() WHERE id='$verification_id'");

        $message = "Email verified successfully. You can now login.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Email - Campus Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="form-container">
    <h2>Verify Email</h2>
    <p style="text-align:center;margin-bottom:15px;">
        <?php echo htmlspecialchars($message); ?>
    </p>
    <p style="text-align:center;">
        <a href="login.html">Login</a>
    </p>
</div>

</body>
</html>
