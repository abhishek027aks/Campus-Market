<?php

session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    die("Please Login First");
}

if(!isset($_GET['id'])){
    die("Invalid Product");
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql = "DELETE FROM products
        WHERE id='$id'
        AND seller_id='$user_id'";

if(mysqli_query($conn,$sql)){
    header("Location: ../frontend/my-products.php");
    exit();
}else{
    echo "Delete Failed";
}

?>