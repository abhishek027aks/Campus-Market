<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../frontend/login.html");
    exit();
}

if(!isset($_GET['id'])){
    die("Notification Missing");
}

$user_id = (int)$_SESSION['user_id'];
$id = (int)$_GET['id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM notifications
     WHERE id=?
     AND user_id=?"
);
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$notification = mysqli_fetch_assoc($result);

if(!$notification){
    die("Notification Not Found");
}

$update_stmt = mysqli_prepare(
    $conn,
    "UPDATE notifications
     SET is_read=1
     WHERE id=?
     AND user_id=?"
);
mysqli_stmt_bind_param($update_stmt, "ii", $id, $user_id);
mysqli_stmt_execute($update_stmt);

$link = trim($notification['link']);

if($link == ""){
    $link = "notifications.php";
}

header("Location: ../frontend/".$link);
exit();
?>
