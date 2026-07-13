<?php
session_start();
include "config.php";

if(!isset($_POST['login'])){
    die("Invalid Request");
}

$email = trim($_POST['email']);
$password = $_POST['password'];

if($email === "" || $password === ""){
    die("Email and password are required");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM users
     WHERE email=?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if($result && mysqli_num_rows($result) > 0){
    $user = mysqli_fetch_assoc($result);

    if(campus_password_matches($password, $user['password'])){
        if(isset($user['email_verified']) && (int)$user['email_verified'] !== 1){
            $_SESSION['verify_message'] = "Please verify your email before login.";
            header("Location: ../frontend/email-verification-sent.php");
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];

        $password_info = password_get_info($user['password']);

        if(!isset($password_info['algoName']) || $password_info['algoName'] === "unknown"){
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $user_id = (int)$user['id'];
            $update_stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
            mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user_id);
            mysqli_stmt_execute($update_stmt);
        }

        header("Location: ../frontend/dashboard.php");
        exit();
    }
}

echo "Invalid Email or Password";
?>
