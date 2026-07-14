<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../frontend/login.html");
    exit();
}

if(!isset($_POST['send_message'])){
    die("Invalid Request");
}

$user_id = (int)$_SESSION['user_id'];
$chat_id = (int)$_POST['chat_id'];
$message = trim($_POST['message']);

if($chat_id <= 0 || $message == ""){
    die("Message Missing");
}

$chat_stmt = mysqli_prepare(
    $conn,
    "SELECT chats.*, products.title
     FROM chats
     JOIN products ON chats.product_id = products.id
     WHERE chats.id=?
     AND (chats.buyer_id=? OR chats.seller_id=?)"
);
mysqli_stmt_bind_param($chat_stmt, "iii", $chat_id, $user_id, $user_id);
mysqli_stmt_execute($chat_stmt);
$chat_result = mysqli_stmt_get_result($chat_stmt);
$chat = mysqli_fetch_assoc($chat_result);

if(!$chat){
    die("Chat Not Found");
}

$receiver_id = $chat['buyer_id'] == $user_id
    ? (int)$chat['seller_id']
    : (int)$chat['buyer_id'];

$insert_stmt = mysqli_prepare(
    $conn,
    "INSERT INTO messages(chat_id, sender_id, receiver_id, message)
     VALUES(?, ?, ?, ?)"
);
mysqli_stmt_bind_param($insert_stmt, "iiis", $chat_id, $user_id, $receiver_id, $message);

if(mysqli_stmt_execute($insert_stmt)){
    $notificationType = "message";
    $notificationTitle = "New Message";
    $notificationMessage = "New message about ".$chat['title'];
    $notificationLink = "chat.php?chat_id=$chat_id";

    $notification_stmt = mysqli_prepare(
        $conn,
        "INSERT INTO notifications(user_id, type, title, message, link)
         VALUES(?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $notification_stmt,
        "issss",
        $receiver_id,
        $notificationType,
        $notificationTitle,
        $notificationMessage,
        $notificationLink
    );
    mysqli_stmt_execute($notification_stmt);

    header("Location: ../frontend/chat.php?chat_id=".$chat_id);
    exit();
}else{
    echo "Message Failed: " . mysqli_error($conn);
}
?>
