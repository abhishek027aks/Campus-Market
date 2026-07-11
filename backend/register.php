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

$safe_email = mysqli_real_escape_string($conn, $email);
$check = mysqli_query($conn, "SELECT id FROM users WHERE email='$safe_email' LIMIT 1");

if($check && mysqli_num_rows($check) > 0){
    die("Email already registered");
}

$safe_fullname = mysqli_real_escape_string($conn, $fullname);
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$safe_password = mysqli_real_escape_string($conn, $hashed_password);

$sql = "INSERT INTO users(fullname,email,password,email_verified)
        VALUES('$safe_fullname','$safe_email','$safe_password',0)";

if(mysqli_query($conn, $sql)){
    $user_id = (int)mysqli_insert_id($conn);
    $token = bin2hex(random_bytes(32));
    $token_hash = mysqli_real_escape_string($conn, hash("sha256", $token));

    mysqli_query(
        $conn,
        "INSERT INTO email_verifications(user_id, token_hash, expires_at)
         VALUES('$user_id', '$token_hash', DATE_ADD(NOW(), INTERVAL 24 HOUR))"
    );

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
