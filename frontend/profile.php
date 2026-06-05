<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_sql = "SELECT * FROM users WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

$product_sql = "SELECT COUNT(*) AS total_products
                FROM products
                WHERE seller_id='$user_id'";

$product_result = mysqli_query($conn, $product_sql);
$product_count = mysqli_fetch_assoc($product_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - Campus Market</title>

<style>

body{
    background:#f4f7fc;
    font-family:Arial, sans-serif;
}

.profile-card{
    width:500px;
    margin:50px auto;
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    text-align:center;
}

.profile-card h1{
    margin-bottom:20px;
}

.info{
    background:#f8f9fa;
    padding:15px;
    margin:10px 0;
    border-radius:8px;
}

.btn{
    display:inline-block;
    margin:10px;
    padding:12px 20px;
    text-decoration:none;
    color:white;
    border-radius:6px;
}

.dashboard-btn{
    background:#0d6efd;
}

.logout-btn{
    background:#dc3545;
}

</style>

</head>
<body>

<div class="profile-card">

    <h1>My Profile</h1>

    <div class="info">
        <strong>Name:</strong>
        <?php echo $user['fullname']; ?>
    </div>

    <div class="info">
        <strong>Email:</strong>
        <?php echo $user['email']; ?>
    </div>

    <div class="info">
        <strong>Total Products:</strong>
        <?php echo $product_count['total_products']; ?>
    </div>

    <a href="dashboard.php" class="btn dashboard-btn">
        Dashboard
    </a>

    <a href="../backend/logout.php" class="btn logout-btn">
        Logout
    </a>

</div>

</body>
</html>