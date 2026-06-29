<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../frontend/login.html");
    exit();
}

if(!isset($_POST['submit_report'])){
    die("Invalid Request");
}

$reporter_id = (int)$_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$reason = $_POST['reason'];
$description = mysqli_real_escape_string($conn, trim($_POST['description']));

$allowed_reasons = [
    "Fake Product",
    "Wrong Information",
    "Inappropriate Content",
    "Duplicate Product",
    "Other"
];

if(!in_array($reason, $allowed_reasons, true)){
    die("Invalid Report Reason");
}

if($description === ""){
    die("Description is required");
}

$product_result = mysqli_query(
    $conn,
    "SELECT id, seller_id
     FROM products
     WHERE id='$product_id'
     AND approval_status='Approved'"
);
$product = $product_result ? mysqli_fetch_assoc($product_result) : null;

if(!$product){
    die("Product Not Found");
}

if((int)$product['seller_id'] === $reporter_id){
    die("Seller cannot report own product");
}

$seller_id = (int)$product['seller_id'];
$safe_reason = mysqli_real_escape_string($conn, $reason);

$sql = "INSERT INTO product_reports
        (product_id, reporter_id, seller_id, reason, description, status)
        VALUES
        ('$product_id', '$reporter_id', '$seller_id', '$safe_reason', '$description', 'Pending')";

if(mysqli_query($conn, $sql)){
    header("Location: ../frontend/product-details.php?id=$product_id&reported=1");
    exit();
}

echo "Report Submit Failed: " . mysqli_error($conn);
?>
