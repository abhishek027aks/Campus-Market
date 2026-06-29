<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

if(!isset($_POST['id'], $_POST['status'])){
    die("Invalid Request");
}

$id = (int)$_POST['id'];
$status = $_POST['status'];
$allowed = ["Approved", "Rejected"];

if(!in_array($status, $allowed, true)){
    die("Invalid Status");
}

$payment_result = mysqli_query(
    $conn,
    "SELECT payments.*, products.title
     FROM payments
     JOIN products ON payments.product_id = products.id
     WHERE payments.id='$id'"
);
$payment = $payment_result ? mysqli_fetch_assoc($payment_result) : null;

if(!$payment){
    die("Payment Not Found");
}

$safe_status = mysqli_real_escape_string($conn, $status);

if(mysqli_query($conn, "UPDATE payments SET status='$safe_status' WHERE id='$id'")){
    $buyer_id = (int)$payment['buyer_id'];
    $seller_id = (int)$payment['seller_id'];
    $product_title = mysqli_real_escape_string($conn, $payment['title']);
    $amount = mysqli_real_escape_string($conn, $payment['amount']);
    $transaction_id = mysqli_real_escape_string($conn, $payment['transaction_id']);

    $buyer_message = mysqli_real_escape_string(
        $conn,
        "Your payment for '$product_title' has been $safe_status."
    );
    $seller_message = mysqli_real_escape_string(
        $conn,
        "Payment for '$product_title' is now $safe_status. Amount: Rs. $amount. Transaction: $transaction_id."
    );

    mysqli_query(
        $conn,
        "INSERT INTO notifications(user_id, type, title, message, link)
         VALUES('$buyer_id', 'payment_status', 'Payment $safe_status', '$buyer_message', 'my-payments.php')"
    );

    mysqli_query(
        $conn,
        "INSERT INTO notifications(user_id, type, title, message, link)
         VALUES('$seller_id', 'payment_status', 'Payment $safe_status', '$seller_message', 'my-products.php')"
    );

    $redirect = "payments.php";

    if(isset($_POST['status_filter']) && $_POST['status_filter'] !== ""){
        $redirect .= "?status=" . urlencode($_POST['status_filter']);
    }

    header("Location: $redirect");
    exit();
}

echo "Update Failed: " . mysqli_error($conn);
?>
