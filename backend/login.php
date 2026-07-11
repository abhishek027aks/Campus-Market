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

$safe_email = mysqli_real_escape_string($conn, $email);

$sql = "SELECT * FROM users
        WHERE email='$safe_email'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

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
            $new_hash = mysqli_real_escape_string(
                $conn,
                password_hash($password, PASSWORD_DEFAULT)
            );
            $user_id = (int)$user['id'];
            mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id='$user_id'");
        }

        header("Location: ../frontend/dashboard.php");
        exit();
    }
}

echo "Invalid Email or Password";
?>
