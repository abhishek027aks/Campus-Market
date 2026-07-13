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
$transaction_id = trim($_POST['transaction_id']);
$allowed_methods = ["UPI", "PhonePe", "Paytm", "Google Pay"];

if(!in_array($payment_method, $allowed_methods, true)){
    die("Invalid Payment Method");
}

if($transaction_id === ""){
    die("Transaction ID is required");
}

$product_stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, price, seller_id
     FROM products
     WHERE id=?
     AND approval_status='Approved'"
);
mysqli_stmt_bind_param($product_stmt, "i", $product_id);
mysqli_stmt_execute($product_stmt);
$product_result = mysqli_stmt_get_result($product_stmt);
$product = $product_result ? mysqli_fetch_assoc($product_result) : null;

if(!$product){
    die("Product Not Found");
}

if((int)$product['seller_id'] === $buyer_id){
    die("Seller cannot pay for own product");
}

$seller_id = (int)$product['seller_id'];
$amount = (float)$product['price'];

$payment_status = "Pending";
$payment_stmt = mysqli_prepare(
    $conn,
    "INSERT INTO payments
     (product_id, buyer_id, seller_id, amount, payment_method, transaction_id, status)
     VALUES
     (?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $payment_stmt,
    "iiidsss",
    $product_id,
    $buyer_id,
    $seller_id,
    $amount,
    $payment_method,
    $transaction_id,
    $payment_status
);

if(mysqli_stmt_execute($payment_stmt)){
    $notification_type = "payment_pending";
    $notification_title = "Payment Submitted";
    $notification_message = "A buyer submitted payment proof for " . $product['title'] . ".";
    $notification_link = "my-products.php";

    $notification_stmt = mysqli_prepare(
        $conn,
        "INSERT INTO notifications(user_id, type, title, message, link)
         VALUES(?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $notification_stmt,
        "issss",
        $seller_id,
        $notification_type,
        $notification_title,
        $notification_message,
        $notification_link
    );
    mysqli_stmt_execute($notification_stmt);

    header("Location: ../frontend/my-payments.php");
    exit();
}

echo "Payment Submit Failed: " . mysqli_error($conn);
?>
