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

$user_stmt = mysqli_prepare(
    $conn,
    "SELECT id, fullname, email_verified
     FROM users
     WHERE email=?
     LIMIT 1"
);
mysqli_stmt_bind_param($user_stmt, "s", $email);
mysqli_stmt_execute($user_stmt);
$result = mysqli_stmt_get_result($user_stmt);
$user = $result ? mysqli_fetch_assoc($result) : null;

if($user){
    if(isset($user['email_verified']) && (int)$user['email_verified'] !== 1){
        $_SESSION['otp_message'] = "Please verify your email before OTP login.";
        header("Location: ../frontend/email-verification-sent.php");
        exit();
    }

    $user_id = (int)$user['id'];
    $otp = (string)random_int(100000, 999999);
    $otp_hash = password_hash($otp, PASSWORD_DEFAULT);

    $expire_stmt = mysqli_prepare(
        $conn,
        "UPDATE login_otps
         SET used_at=NOW()
         WHERE user_id=?
         AND used_at IS NULL"
    );
    mysqli_stmt_bind_param($expire_stmt, "i", $user_id);
    mysqli_stmt_execute($expire_stmt);

    $insert_stmt = mysqli_prepare(
        $conn,
        "INSERT INTO login_otps(user_id, otp_hash, expires_at)
         VALUES(?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))"
    );
    mysqli_stmt_bind_param($insert_stmt, "is", $user_id, $otp_hash);
    mysqli_stmt_execute($insert_stmt);

    $_SESSION['otp_login_user_id'] = $user_id;

    if(isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], ["localhost", "127.0.0.1"], true)){
        $_SESSION['dev_login_otp'] = $otp;
    }
}

$_SESSION['otp_message'] = "If this email exists, an OTP will be prepared.";
header("Location: ../frontend/verify-login-otp.php");
exit();
?>
