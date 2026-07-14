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
$comment = trim($_POST['comment']);

if($product_id <= 0){
    die("Invalid Product");
}

if($rating < 1 || $rating > 5){
    die("Rating must be between 1 and 5");
}

if($comment == ""){
    die("Comment is required");
}

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
    die("Product Not Found");
}

$review_stmt = mysqli_prepare(
    $conn,
    "INSERT INTO product_reviews(user_id, product_id, rating, comment)
     VALUES(?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
     rating=VALUES(rating),
     comment=VALUES(comment)"
);

mysqli_stmt_bind_param($review_stmt, "iiis", $user_id, $product_id, $rating, $comment);

if(mysqli_stmt_execute($review_stmt)){
    header("Location: ../frontend/product-details.php?id=".$product_id."#reviews");
    exit();
}else{
    echo "Review Failed: " . mysqli_error($conn);
}
?>
