<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

if(!isset($_POST['report_id'], $_POST['action'])){
    die("Invalid Request");
}

$report_id = (int)$_POST['report_id'];
$action = $_POST['action'];
$allowed_actions = ["reject_product", "delete_product"];

if(!in_array($action, $allowed_actions, true)){
    die("Invalid Action");
}

$report_result = mysqli_query(
    $conn,
    "SELECT product_reports.*,
            products.title,
            products.image
     FROM product_reports
     JOIN products ON product_reports.product_id = products.id
     WHERE product_reports.id='$report_id'"
);
$report = $report_result ? mysqli_fetch_assoc($report_result) : null;

if(!$report){
    die("Report Not Found");
}

$product_id = (int)$report['product_id'];
$seller_id = (int)$report['seller_id'];
$title = mysqli_real_escape_string($conn, $report['title']);
$redirect = "reports.php";

if(isset($_POST['status_filter']) && $_POST['status_filter'] !== ""){
    $redirect .= "?status=" . urlencode($_POST['status_filter']);
}

if($action === "reject_product"){
    if(mysqli_query($conn, "UPDATE products SET approval_status='Rejected' WHERE id='$product_id'")){
        mysqli_query(
            $conn,
            "UPDATE product_reports
             SET status='Reviewed', reviewed_at=NOW()
             WHERE product_id='$product_id'"
        );

        $message = mysqli_real_escape_string(
            $conn,
            "Your product '$title' was rejected after admin reviewed a product report."
        );

        mysqli_query(
            $conn,
            "INSERT INTO notifications(user_id, type, title, message, link)
             VALUES('$seller_id', 'product_report_action', 'Product Rejected', '$message', 'my-products.php')"
        );

        header("Location: $redirect");
        exit();
    }
}

if($action === "delete_product"){
    $file_name = basename($report['image']);
    $file_path = __DIR__ . "/../frontend/uploads/" . $file_name;

    if(mysqli_query($conn, "DELETE FROM products WHERE id='$product_id'")){
        mysqli_query(
            $conn,
            "UPDATE product_reports
             SET status='Reviewed', reviewed_at=NOW()
             WHERE product_id='$product_id'"
        );

        if($file_name !== "" && is_file($file_path)){
            unlink($file_path);
        }

        $message = mysqli_real_escape_string(
            $conn,
            "Your product '$title' was deleted after admin reviewed a product report."
        );

        mysqli_query(
            $conn,
            "INSERT INTO notifications(user_id, type, title, message, link)
             VALUES('$seller_id', 'product_report_action', 'Product Deleted', '$message', 'my-products.php')"
        );

        header("Location: $redirect");
        exit();
    }
}

echo "Action Failed: " . mysqli_error($conn);
?>
