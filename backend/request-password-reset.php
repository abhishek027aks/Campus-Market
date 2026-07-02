<?php
session_start();
include "config.php";

if(!isset($_POST['request_reset'])){
    die("Invalid Request");
}

$email = trim($_POST['email']);

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $_SESSION['reset_message'] = "If this email exists, a reset link will be prepared.";
    header("Location: ../frontend/forgot-password.php");
    exit();
}

$safe_email = mysqli_real_escape_string($conn, $email);
$result = mysqli_query($conn, "SELECT id, email FROM users WHERE email='$safe_email' LIMIT 1");
$user = $result ? mysqli_fetch_assoc($result) : null;

if($user){
    $user_id = (int)$user['id'];
    $token = bin2hex(random_bytes(32));
    $token_hash = hash("sha256", $token);
    $safe_token_hash = mysqli_real_escape_string($conn, $token_hash);

    mysqli_query(
        $conn,
        "UPDATE password_resets
         SET used_at=NOW()
         WHERE user_id='$user_id'
         AND used_at IS NULL"
    );

    mysqli_query(
        $conn,
        "INSERT INTO password_resets(user_id, token_hash, expires_at)
         VALUES('$user_id', '$safe_token_hash', DATE_ADD(NOW(), INTERVAL 30 MINUTE))"
    );

    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost";
    $path = dirname(dirname($_SERVER['SCRIPT_NAME'])) . "/frontend/reset-password.php?token=$token";
    $reset_link = "http://$host$path";

    if(in_array($_SERVER['SERVER_NAME'], ["localhost", "127.0.0.1"], true)){
        $_SESSION['dev_reset_link'] = $reset_link;
    }
}

$_SESSION['reset_message'] = "If this email exists, a reset link will be prepared.";
header("Location: ../frontend/forgot-password.php");
exit();
?>
