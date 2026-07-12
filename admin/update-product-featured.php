<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

campus_require_admin_permission($conn, "products.feature");

if(!isset($_POST['id'], $_POST['featured'])){
    die("Invalid Request");
}

$id = (int)$_POST['id'];
$featured = (int)$_POST['featured'] === 1 ? 1 : 0;

$product_result = mysqli_query(
    $conn,
    "SELECT id, title FROM products WHERE id='$id'"
);
$product = $product_result ? mysqli_fetch_assoc($product_result) : null;

if(!$product){
    die("Product Not Found");
}

if(mysqli_query($conn, "UPDATE products SET is_featured='$featured' WHERE id='$id'")){
    $redirect = "products.php";

    if(isset($_POST['status_filter']) && $_POST['status_filter'] !== ""){
        $redirect .= "?status=" . urlencode($_POST['status_filter']);
    }

    header("Location: $redirect");
    exit();
}

echo "Update Failed: " . mysqli_error($conn);
?>
