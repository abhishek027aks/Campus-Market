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
$message = mysqli_real_escape_string($conn, trim($_POST['message']));

if($chat_id <= 0 || $message == ""){
    die("Message Missing");
}

$chat_sql = "SELECT chats.*, products.title
             FROM chats
             JOIN products ON chats.product_id = products.id
             WHERE chats.id='$chat_id'
             AND (chats.buyer_id='$user_id' OR chats.seller_id='$user_id')";

$chat_result = mysqli_query($conn, $chat_sql);
$chat = mysqli_fetch_assoc($chat_result);

if(!$chat){
    die("Chat Not Found");
}

$receiver_id = $chat['buyer_id'] == $user_id
    ? (int)$chat['seller_id']
    : (int)$chat['buyer_id'];

$insert_sql = "INSERT INTO messages(chat_id, sender_id, receiver_id, message)
               VALUES('$chat_id', '$user_id', '$receiver_id', '$message')";

if(mysqli_query($conn, $insert_sql)){
    $productTitle = mysqli_real_escape_string($conn, $chat['title']);
    $notificationMessage = mysqli_real_escape_string(
        $conn,
        "New message about ".$chat['title']
    );

    mysqli_query(
        $conn,
        "INSERT INTO notifications(user_id, type, title, message, link)
         VALUES(
            '$receiver_id',
            'message',
            'New Message',
            '$notificationMessage',
            'chat.php?chat_id=$chat_id'
         )"
    );

    header("Location: ../frontend/chat.php?chat_id=".$chat_id);
    exit();
}else{
    echo "Message Failed: " . mysqli_error($conn);
}
?>
