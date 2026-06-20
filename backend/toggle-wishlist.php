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

$check_sql = "SELECT id FROM wishlist
              WHERE user_id='$user_id'
              AND product_id='$product_id'";

$check_result = mysqli_query($conn, $check_sql);

if($check_result && mysqli_num_rows($check_result) > 0){
    mysqli_query(
        $conn,
        "DELETE FROM wishlist
         WHERE user_id='$user_id'
         AND product_id='$product_id'"
    );
}else{
    $product_check = mysqli_query(
        $conn,
        "SELECT id FROM products
         WHERE id='$product_id'
         AND approval_status='Approved'"
    );

    if(!$product_check || mysqli_num_rows($product_check) == 0){
        die("Product is not available");
    }

    mysqli_query(
        $conn,
        "INSERT INTO wishlist(user_id, product_id)
         VALUES('$user_id', '$product_id')"
    );
}

header("Location: ".$redirect);
exit();
?>
