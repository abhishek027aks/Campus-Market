<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$chat_id = 0;

if(isset($_GET['chat_id'])){
    $chat_id = (int)$_GET['chat_id'];
}
elseif(isset($_GET['product_id'])){
    $product_id = (int)$_GET['product_id'];

    $product_sql = "SELECT * FROM products
                    WHERE id='$product_id'
                    AND approval_status='Approved'";
    $product_result = mysqli_query($conn, $product_sql);
    $product = mysqli_fetch_assoc($product_result);

    if(!$product){
        die("Product Not Found");
    }

    if((int)$product['seller_id'] == $user_id){
        die("You cannot chat with yourself for your own product.");
    }

    $seller_id = (int)$product['seller_id'];

    $check_sql = "SELECT id FROM chats
                  WHERE product_id='$product_id'
                  AND buyer_id='$user_id'
                  AND seller_id='$seller_id'";

    $check_result = mysqli_query($conn, $check_sql);

    if($check_result && mysqli_num_rows($check_result) > 0){
        $chat = mysqli_fetch_assoc($check_result);
        $chat_id = (int)$chat['id'];
    }else{
        mysqli_query(
            $conn,
            "INSERT INTO chats(product_id, buyer_id, seller_id)
             VALUES('$product_id', '$user_id', '$seller_id')"
        );

        $chat_id = mysqli_insert_id($conn);
    }
}
else{
    die("Chat Not Found");
}

$chat_sql = "SELECT chats.*, products.title, products.preview_image, products.image,
             buyer.fullname AS buyer_name,
             seller.fullname AS seller_name
             FROM chats
             JOIN products ON chats.product_id = products.id
             JOIN users AS buyer ON chats.buyer_id = buyer.id
             JOIN users AS seller ON chats.seller_id = seller.id
             WHERE chats.id='$chat_id'
             AND (chats.buyer_id='$user_id' OR chats.seller_id='$user_id')";

$chat_result = mysqli_query($conn, $chat_sql);
$chat = mysqli_fetch_assoc($chat_result);

if(!$chat){
    die("Chat Not Found");
}

mysqli_query(
    $conn,
    "UPDATE messages
     SET is_read=1
     WHERE chat_id='$chat_id'
     AND receiver_id='$user_id'"
);

$messages_sql = "SELECT messages.*, users.fullname
                 FROM messages
                 JOIN users ON messages.sender_id = users.id
                 WHERE messages.chat_id='$chat_id'
                 ORDER BY messages.id ASC";

$messages_result = mysqli_query($conn, $messages_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat - Campus Market</title>
<link rel="stylesheet" href="css/style.css">

<style>
body{
    background:#f4f7fc;
}

.chat-wrap{
    width:850px;
    max-width:95%;
    margin:35px auto;
    background:white;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    overflow:hidden;
}

.chat-head{
    padding:18px;
    background:#0d6efd;
    color:white;
}

.chat-head a{
    color:white;
}

.chat-meta{
    padding:15px 18px;
    background:#f8f9fa;
    border-bottom:1px solid #e5e7eb;
}

.messages{
    padding:18px;
    min-height:320px;
}

.message-row{
    margin-bottom:12px;
    display:flex;
}

.message-row.mine{
    justify-content:flex-end;
}

.bubble{
    max-width:70%;
    padding:12px;
    border-radius:10px;
    background:#eef3f8;
}

.mine .bubble{
    background:#0d6efd;
    color:white;
}

.message-name{
    font-size:13px;
    font-weight:bold;
    margin-bottom:5px;
}

.message-time{
    font-size:12px;
    color:#666;
    margin-top:6px;
}

.mine .message-time{
    color:#e9ecef;
}

.chat-form{
    padding:18px;
    border-top:1px solid #e5e7eb;
    background:#fff;
}

.chat-form textarea{
    width:100%;
    min-height:90px;
    padding:12px;
    border:1px solid #ddd;
    border-radius:8px;
    resize:vertical;
}

.btn{
    display:inline-block;
    padding:10px 15px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    border:none;
    cursor:pointer;
    margin-top:10px;
}

.send{
    background:#198754;
}

.back{
    background:#6f42c1;
}
</style>
</head>
<body>

<div class="chat-wrap">
    <div class="chat-head">
        <h1>Product Chat</h1>
        <p>
            Product:
            <a href="product-details.php?id=<?php echo (int)$chat['product_id']; ?>">
                <?php echo htmlspecialchars($chat['title']); ?>
            </a>
        </p>
    </div>

    <div class="chat-meta">
        <p><b>Buyer:</b> <?php echo htmlspecialchars($chat['buyer_name']); ?></p>
        <p><b>Seller:</b> <?php echo htmlspecialchars($chat['seller_name']); ?></p>
    </div>

    <div class="messages">
        <?php if(mysqli_num_rows($messages_result) == 0){ ?>
            <p>No messages yet. Start the conversation.</p>
        <?php } ?>

        <?php while($message = mysqli_fetch_assoc($messages_result)){ ?>
            <div class="message-row <?php echo (int)$message['sender_id'] == $user_id ? 'mine' : ''; ?>">
                <div class="bubble">
                    <div class="message-name">
                        <?php echo htmlspecialchars($message['fullname']); ?>
                    </div>

                    <div>
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>

                    <div class="message-time">
                        <?php echo date("d M Y, h:i A", strtotime($message['created_at'])); ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <form
    class="chat-form"
    action="../backend/messages.php"
    method="POST">
        <input
        type="hidden"
        name="chat_id"
        value="<?php echo (int)$chat_id; ?>">

        <textarea
        name="message"
        placeholder="Write message"
        required></textarea>

        <button
        type="submit"
        name="send_message"
        class="btn send">
            Send Message
        </button>

        <a class="btn back" href="dashboard.php">Dashboard</a>
    </form>
</div>

</body>
</html>
