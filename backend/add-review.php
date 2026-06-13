<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../frontend/login.html");
    exit();
}

if(!isset($_POST['submit_review'])){
    die("Invalid Request");
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$rating = (int)$_POST['rating'];
$comment = mysqli_real_escape_string($conn, trim($_POST['comment']));

if($product_id <= 0){
    die("Invalid Product");
}

if($rating < 1 || $rating > 5){
    die("Rating must be between 1 and 5");
}

if($comment == ""){
    die("Comment is required");
}

$product_check = mysqli_query(
    $conn,
    "SELECT id FROM products WHERE id='$product_id'"
);

if(!$product_check || mysqli_num_rows($product_check) == 0){
    die("Product Not Found");
}

$sql = "INSERT INTO product_reviews(user_id, product_id, rating, comment)
        VALUES('$user_id', '$product_id', '$rating', '$comment')
        ON DUPLICATE KEY UPDATE
        rating='$rating',
        comment='$comment'";

if(mysqli_query($conn, $sql)){
    header("Location: ../frontend/product-details.php?id=".$product_id."#reviews");
    exit();
}else{
    echo "Review Failed: " . mysqli_error($conn);
}
?>
