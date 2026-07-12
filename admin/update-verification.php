<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

campus_require_admin_permission($conn, "users.verify");

if(!isset($_GET['id']) || !isset($_GET['status'])){
    die("Invalid Request");
}

$id = (int)$_GET['id'];
$status = $_GET['status'];
$allowed = ["Approved", "Rejected", "Pending", "Not Submitted"];

if(!in_array($status, $allowed)){
    die("Invalid Status");
}

$status = mysqli_real_escape_string($conn, $status);

$sql = "UPDATE users
        SET verification_status='$status'
        WHERE id='$id'";

if(mysqli_query($conn, $sql)){
    header("Location: users.php");
    exit();
}else{
    echo "Update Failed: " . mysqli_error($conn);
}
?>
