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

$sql = "INSERT INTO users(fullname,email,password)
        VALUES('$safe_fullname','$safe_email','$safe_password')";

if(mysqli_query($conn, $sql)){
    header("Location: ../frontend/login.html?registered=1");
    exit();
}

echo "Registration Failed: " . mysqli_error($conn);
?>
