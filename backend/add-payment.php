<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../frontend/login.html");
    exit();
}

if(!isset($_POST['submit_payment'])){
    die("Invalid Request");
}

$buyer_id = (int)$_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$payment_method = $_POST['payment_method'];
$transaction_id = mysqli_real_escape_string($conn, trim($_POST['transaction_id']));
$allowed_methods = ["UPI", "PhonePe", "Paytm", "Google Pay"];

if(!in_array($payment_method, $allowed_methods, true)){
    die("Invalid Payment Method");
}

if($transaction_id === ""){
    die("Transaction ID is required");
}

$product_result = mysqli_query(
    $conn,
    "SELECT id, title, price, seller_id
     FROM products
     WHERE id='$product_id'
     AND approval_status='Approved'"
);
$product = $product_result ? mysqli_fetch_assoc($product_result) : null;

if(!$product){
    die("Product Not Found");
}

if((int)$product['seller_id'] === $buyer_id){
    die("Seller cannot pay for own product");
}

$seller_id = (int)$product['seller_id'];
$amount = mysqli_real_escape_string($conn, $product['price']);
$safe_method = mysqli_real_escape_string($conn, $payment_method);

$sql = "INSERT INTO payments
        (product_id, buyer_id, seller_id, amount, payment_method, transaction_id, status)
        VALUES
        ('$product_id', '$buyer_id', '$seller_id', '$amount', '$safe_method', '$transaction_id', 'Pending')";

if(mysqli_query($conn, $sql)){
    $title = mysqli_real_escape_string($conn, $product['title']);

    mysqli_query(
        $conn,
        "INSERT INTO notifications(user_id, type, title, message, link)
         VALUES(
            '$seller_id',
            'payment_pending',
            'Payment Submitted',
            'A buyer submitted payment proof for $title.',
            'my-products.php'
         )"
    );

    header("Location: ../frontend/my-payments.php");
    exit();
}

echo "Payment Submit Failed: " . mysqli_error($conn);
?>
