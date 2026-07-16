<?php

session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    die("Please Login First");
}

if(!isset($_GET['id'])){
    die("Invalid Product");
}

$id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM products
     WHERE id=?
     AND seller_id=?"
);
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);

if(mysqli_stmt_execute($stmt)){
    header("Location: ../frontend/my-products.php");
    exit();
}else{
    echo "Delete Failed";
}

?>
