<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

campus_require_admin_permission($conn, "products.moderate");

if(!isset($_POST['id'], $_POST['status'])){
    die("Invalid Request");
}

$id = (int)$_POST['id'];
$status = $_POST['status'];
$allowed = ["Approved", "Rejected"];

if(!in_array($status, $allowed, true)){
    die("Invalid Status");
}

$product_result = mysqli_query(
    $conn,
    "SELECT id, title, seller_id FROM products WHERE id='$id'"
);
$product = $product_result ? mysqli_fetch_assoc($product_result) : null;

if(!$product){
    die("Product Not Found");
}

$safe_status = mysqli_real_escape_string($conn, $status);

if(mysqli_query($conn, "UPDATE products SET approval_status='$safe_status' WHERE id='$id'")){
    $seller_id = (int)$product['seller_id'];
    $title = mysqli_real_escape_string($conn, $product['title']);
    $notification_title = "Product $safe_status";
    $message = mysqli_real_escape_string(
        $conn,
        "Your product '$title' has been $safe_status by the admin."
    );
    $link = "my-products.php";

    mysqli_query(
        $conn,
        "INSERT INTO notifications(user_id, type, title, message, link)
         VALUES('$seller_id', 'product_approval', '$notification_title', '$message', '$link')"
    );

    header("Location: products.php");
    exit();
}

echo "Update Failed: " . mysqli_error($conn);
?>
