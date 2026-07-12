<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

campus_require_admin_permission($conn, "reports.review");

if(!isset($_POST['id'], $_POST['status'])){
    die("Invalid Request");
}

$id = (int)$_POST['id'];
$status = $_POST['status'];

if($status !== "Reviewed"){
    die("Invalid Status");
}

$report_result = mysqli_query(
    $conn,
    "SELECT id FROM product_reports WHERE id='$id'"
);
$report = $report_result ? mysqli_fetch_assoc($report_result) : null;

if(!$report){
    die("Report Not Found");
}

if(mysqli_query($conn, "UPDATE product_reports SET status='Reviewed', reviewed_at=NOW() WHERE id='$id'")){
    $redirect = "reports.php";

    if(isset($_POST['status_filter']) && $_POST['status_filter'] !== ""){
        $redirect .= "?status=" . urlencode($_POST['status_filter']);
    }

    header("Location: $redirect");
    exit();
}

echo "Update Failed: " . mysqli_error($conn);
?>
