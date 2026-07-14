<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../frontend/login.html");
    exit();
}

if(!isset($_GET['product_id'])){
    die("Product Missing");
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)$_GET['product_id'];
$redirect = "../frontend/product-details.php?id=".$product_id;

if(isset($_GET['redirect']) && $_GET['redirect'] == "wishlist"){
    $redirect = "../frontend/my-wishlist.php";
}

$check_stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM wishlist
     WHERE user_id=?
     AND product_id=?"
);
mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $product_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if($check_result && mysqli_num_rows($check_result) > 0){
    $delete_stmt = mysqli_prepare(
        $conn,
        "DELETE FROM wishlist
         WHERE user_id=?
         AND product_id=?"
    );
    mysqli_stmt_bind_param($delete_stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($delete_stmt);
}else{
    $product_stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM products
         WHERE id=?
         AND approval_status='Approved'"
    );
    mysqli_stmt_bind_param($product_stmt, "i", $product_id);
    mysqli_stmt_execute($product_stmt);
    $product_check = mysqli_stmt_get_result($product_stmt);

    if(!$product_check || mysqli_num_rows($product_check) == 0){
        die("Product is not available");
    }

    $insert_stmt = mysqli_prepare(
        $conn,
        "INSERT INTO wishlist(user_id, product_id)
         VALUES(?, ?)"
    );
    mysqli_stmt_bind_param($insert_stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($insert_stmt);
}

header("Location: ".$redirect);
exit();
?>
