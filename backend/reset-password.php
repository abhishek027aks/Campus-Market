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

$token_hash = mysqli_real_escape_string($conn, hash("sha256", $token));

$reset_result = mysqli_query(
    $conn,
    "SELECT * FROM password_resets
     WHERE token_hash='$token_hash'
     AND used_at IS NULL
     AND expires_at > NOW()
     ORDER BY id DESC
     LIMIT 1"
);
$reset = $reset_result ? mysqli_fetch_assoc($reset_result) : null;

if(!$reset){
    $_SESSION['reset_error'] = "Reset link is invalid or expired";
    header("Location: ../frontend/reset-password.php?token=" . urlencode($token));
    exit();
}

$user_id = (int)$reset['user_id'];
$reset_id = (int)$reset['id'];
$safe_password = mysqli_real_escape_string(
    $conn,
    password_hash($password, PASSWORD_DEFAULT)
);

mysqli_query($conn, "UPDATE users SET password='$safe_password' WHERE id='$user_id'");
mysqli_query($conn, "UPDATE password_resets SET used_at=NOW() WHERE id='$reset_id'");

header("Location: ../frontend/login.html?password_reset=1");
exit();
?>
