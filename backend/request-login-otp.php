<?php
session_start();
include "config.php";

if(!isset($_POST['request_login_otp'])){
    die("Invalid Request");
}

$email = trim($_POST['email']);

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $_SESSION['otp_message'] = "If this email exists, an OTP will be prepared.";
    header("Location: ../frontend/otp-login.php");
    exit();
}

$safe_email = mysqli_real_escape_string($conn, $email);
$result = mysqli_query(
    $conn,
    "SELECT id, fullname, email_verified
     FROM users
     WHERE email='$safe_email'
     LIMIT 1"
);
$user = $result ? mysqli_fetch_assoc($result) : null;

if($user){
    if(isset($user['email_verified']) && (int)$user['email_verified'] !== 1){
        $_SESSION['otp_message'] = "Please verify your email before OTP login.";
        header("Location: ../frontend/email-verification-sent.php");
        exit();
    }

    $user_id = (int)$user['id'];
    $otp = (string)random_int(100000, 999999);
    $otp_hash = mysqli_real_escape_string($conn, password_hash($otp, PASSWORD_DEFAULT));

    mysqli_query(
        $conn,
        "UPDATE login_otps
         SET used_at=NOW()
         WHERE user_id='$user_id'
         AND used_at IS NULL"
    );

    mysqli_query(
        $conn,
        "INSERT INTO login_otps(user_id, otp_hash, expires_at)
         VALUES('$user_id', '$otp_hash', DATE_ADD(NOW(), INTERVAL 10 MINUTE))"
    );

    $_SESSION['otp_login_user_id'] = $user_id;

    if(isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], ["localhost", "127.0.0.1"], true)){
        $_SESSION['dev_login_otp'] = $otp;
    }
}

$_SESSION['otp_message'] = "If this email exists, an OTP will be prepared.";
header("Location: ../frontend/verify-login-otp.php");
exit();
?>
