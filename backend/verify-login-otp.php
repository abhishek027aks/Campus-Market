<?php
session_start();
include "config.php";

if(!isset($_POST['verify_login_otp'])){
    die("Invalid Request");
}

if(!isset($_SESSION['otp_login_user_id'])){
    $_SESSION['otp_message'] = "Please request a fresh OTP.";
    header("Location: ../frontend/otp-login.php");
    exit();
}

$otp = trim($_POST['otp']);

if(!preg_match('/^[0-9]{6}$/', $otp)){
    $_SESSION['otp_message'] = "Enter a valid 6-digit OTP.";
    header("Location: ../frontend/verify-login-otp.php");
    exit();
}

$user_id = (int)$_SESSION['otp_login_user_id'];

$otp_result = mysqli_query(
    $conn,
    "SELECT id, otp_hash, attempts
     FROM login_otps
     WHERE user_id='$user_id'
     AND used_at IS NULL
     AND expires_at > NOW()
     ORDER BY id DESC
     LIMIT 1"
);
$otp_row = $otp_result ? mysqli_fetch_assoc($otp_result) : null;

if(!$otp_row){
    unset($_SESSION['otp_login_user_id']);
    $_SESSION['otp_message'] = "OTP expired. Please request a new OTP.";
    header("Location: ../frontend/otp-login.php");
    exit();
}

$otp_id = (int)$otp_row['id'];

if((int)$otp_row['attempts'] >= 5){
    mysqli_query($conn, "UPDATE login_otps SET used_at=NOW() WHERE id='$otp_id'");
    unset($_SESSION['otp_login_user_id']);
    $_SESSION['otp_message'] = "Too many attempts. Please request a new OTP.";
    header("Location: ../frontend/otp-login.php");
    exit();
}

if(!password_verify($otp, $otp_row['otp_hash'])){
    mysqli_query(
        $conn,
        "UPDATE login_otps
         SET attempts=attempts+1
         WHERE id='$otp_id'"
    );
    $_SESSION['otp_message'] = "Invalid OTP. Please try again.";
    header("Location: ../frontend/verify-login-otp.php");
    exit();
}

$user_result = mysqli_query(
    $conn,
    "SELECT id, fullname, email_verified
     FROM users
     WHERE id='$user_id'
     LIMIT 1"
);
$user = $user_result ? mysqli_fetch_assoc($user_result) : null;

if(!$user || (isset($user['email_verified']) && (int)$user['email_verified'] !== 1)){
    unset($_SESSION['otp_login_user_id']);
    $_SESSION['otp_message'] = "Unable to complete OTP login.";
    header("Location: ../frontend/otp-login.php");
    exit();
}

mysqli_query($conn, "UPDATE login_otps SET used_at=NOW() WHERE id='$otp_id'");

$_SESSION['user_id'] = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
unset($_SESSION['otp_login_user_id']);

header("Location: ../frontend/dashboard.php");
exit();
?>
