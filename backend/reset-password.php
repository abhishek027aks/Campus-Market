<?php
session_start();
include "config.php";

if(!isset($_POST['reset_password'])){
    die("Invalid Request");
}

$token = isset($_POST['token']) ? trim($_POST['token']) : "";
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if($token === "" || $password === "" || $confirm_password === ""){
    $_SESSION['reset_error'] = "All fields are required";
    header("Location: ../frontend/reset-password.php?token=" . urlencode($token));
    exit();
}

if($password !== $confirm_password){
    $_SESSION['reset_error'] = "Passwords do not match";
    header("Location: ../frontend/reset-password.php?token=" . urlencode($token));
    exit();
}

if(strlen($password) < 6){
    $_SESSION['reset_error'] = "Password must be at least 6 characters";
    header("Location: ../frontend/reset-password.php?token=" . urlencode($token));
    exit();
}

$token_hash = hash("sha256", $token);

$reset_stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM password_resets
     WHERE token_hash=?
     AND used_at IS NULL
     AND expires_at > NOW()
     ORDER BY id DESC
     LIMIT 1"
);
mysqli_stmt_bind_param($reset_stmt, "s", $token_hash);
mysqli_stmt_execute($reset_stmt);
$reset_result = mysqli_stmt_get_result($reset_stmt);
$reset = $reset_result ? mysqli_fetch_assoc($reset_result) : null;

if(!$reset){
    $_SESSION['reset_error'] = "Reset link is invalid or expired";
    header("Location: ../frontend/reset-password.php?token=" . urlencode($token));
    exit();
}

$user_id = (int)$reset['user_id'];
$reset_id = (int)$reset['id'];
$safe_password = password_hash($password, PASSWORD_DEFAULT);

$user_stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
mysqli_stmt_bind_param($user_stmt, "si", $safe_password, $user_id);
mysqli_stmt_execute($user_stmt);

$used_stmt = mysqli_prepare($conn, "UPDATE password_resets SET used_at=NOW() WHERE id=?");
mysqli_stmt_bind_param($used_stmt, "i", $reset_id);
mysqli_stmt_execute($used_stmt);

header("Location: ../frontend/login.html?password_reset=1");
exit();
?>
