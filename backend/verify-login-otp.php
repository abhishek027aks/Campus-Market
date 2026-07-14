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

$otp_stmt = mysqli_prepare(
    $conn,
    "SELECT id, otp_hash, attempts
     FROM login_otps
     WHERE user_id=?
     AND used_at IS NULL
     AND expires_at > NOW()
     ORDER BY id DESC
     LIMIT 1"
);
mysqli_stmt_bind_param($otp_stmt, "i", $user_id);
mysqli_stmt_execute($otp_stmt);
$otp_result = mysqli_stmt_get_result($otp_stmt);
$otp_row = $otp_result ? mysqli_fetch_assoc($otp_result) : null;

if(!$otp_row){
    unset($_SESSION['otp_login_user_id']);
    $_SESSION['otp_message'] = "OTP expired. Please request a new OTP.";
    header("Location: ../frontend/otp-login.php");
    exit();
}

$otp_id = (int)$otp_row['id'];

if((int)$otp_row['attempts'] >= 5){
    $used_stmt = mysqli_prepare($conn, "UPDATE login_otps SET used_at=NOW() WHERE id=?");
    mysqli_stmt_bind_param($used_stmt, "i", $otp_id);
    mysqli_stmt_execute($used_stmt);
    unset($_SESSION['otp_login_user_id']);
    $_SESSION['otp_message'] = "Too many attempts. Please request a new OTP.";
    header("Location: ../frontend/otp-login.php");
    exit();
}

if(!password_verify($otp, $otp_row['otp_hash'])){
    $attempt_stmt = mysqli_prepare(
        $conn,
        "UPDATE login_otps
         SET attempts=attempts+1
         WHERE id=?"
    );
    mysqli_stmt_bind_param($attempt_stmt, "i", $otp_id);
    mysqli_stmt_execute($attempt_stmt);
    $_SESSION['otp_message'] = "Invalid OTP. Please try again.";
    header("Location: ../frontend/verify-login-otp.php");
    exit();
}

$user_stmt = mysqli_prepare(
    $conn,
    "SELECT id, fullname, email_verified
     FROM users
     WHERE id=?
     LIMIT 1"
);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = $user_result ? mysqli_fetch_assoc($user_result) : null;

if(!$user || (isset($user['email_verified']) && (int)$user['email_verified'] !== 1)){
    unset($_SESSION['otp_login_user_id']);
    $_SESSION['otp_message'] = "Unable to complete OTP login.";
    header("Location: ../frontend/otp-login.php");
    exit();
}

$used_stmt = mysqli_prepare($conn, "UPDATE login_otps SET used_at=NOW() WHERE id=?");
mysqli_stmt_bind_param($used_stmt, "i", $otp_id);
mysqli_stmt_execute($used_stmt);

$_SESSION['user_id'] = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
unset($_SESSION['otp_login_user_id']);

header("Location: ../frontend/dashboard.php");
exit();
?>
