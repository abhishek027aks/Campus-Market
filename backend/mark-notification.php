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

$sql = "SELECT * FROM notifications
        WHERE id='$id'
        AND user_id='$user_id'";

$result = mysqli_query($conn, $sql);
$notification = mysqli_fetch_assoc($result);

if(!$notification){
    die("Notification Not Found");
}

mysqli_query(
    $conn,
    "UPDATE notifications
     SET is_read=1
     WHERE id='$id'
     AND user_id='$user_id'"
);

$link = trim($notification['link']);

if($link == ""){
    $link = "notifications.php";
}

header("Location: ../frontend/".$link);
exit();
?>
