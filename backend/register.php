<?php
session_start();
include "config.php";

if(!isset($_POST['register'])){
    die("Invalid Request");
}

$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$password = $_POST['password'];

if($fullname === "" || $email === "" || $password === ""){
    die("All fields are required");
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    die("Invalid Email");
}

$check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? LIMIT 1");
mysqli_stmt_bind_param($check_stmt, "s", $email);
mysqli_stmt_execute($check_stmt);
$check = mysqli_stmt_get_result($check_stmt);

if($check && mysqli_num_rows($check) > 0){
    die("Email already registered");
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$insert_stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users(fullname,email,password,email_verified)
     VALUES(?,?,?,0)"
);

mysqli_stmt_bind_param($insert_stmt, "sss", $fullname, $email, $hashed_password);

if(mysqli_stmt_execute($insert_stmt)){
    $user_id = (int)mysqli_insert_id($conn);
    $token = bin2hex(random_bytes(32));
    $token_hash = hash("sha256", $token);

    $verify_stmt = mysqli_prepare(
        $conn,
        "INSERT INTO email_verifications(user_id, token_hash, expires_at)
         VALUES(?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))"
    );
    mysqli_stmt_bind_param($verify_stmt, "is", $user_id, $token_hash);
    mysqli_stmt_execute($verify_stmt);

    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost";
    $path = dirname(dirname($_SERVER['SCRIPT_NAME'])) . "/frontend/verify-email.php?token=$token";
    $verify_link = "http://$host$path";

    if(in_array($_SERVER['SERVER_NAME'], ["localhost", "127.0.0.1"], true)){
        $_SESSION['dev_verify_link'] = $verify_link;
    }

    $_SESSION['verify_message'] = "Account created. Please verify your email before login.";
    header("Location: ../frontend/email-verification-sent.php");
    exit();
}

echo "Registration Failed: " . mysqli_error($conn);
?>
