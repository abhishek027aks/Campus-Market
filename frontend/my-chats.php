<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$sql = "SELECT chats.*, products.title,
        buyer.fullname AS buyer_name,
        seller.fullname AS seller_name,
        (
            SELECT message
            FROM messages
            WHERE messages.chat_id = chats.id
            ORDER BY messages.id DESC
            LIMIT 1
        ) AS last_message,
        (
            SELECT COUNT(*)
            FROM messages
            WHERE messages.chat_id = chats.id
            AND messages.receiver_id = '$user_id'
            AND messages.is_read = 0
        ) AS unread_count
        FROM chats
        JOIN products ON chats.product_id = products.id
        JOIN users AS buyer ON chats.buyer_id = buyer.id
        JOIN users AS seller ON chats.seller_id = seller.id
        WHERE chats.buyer_id='$user_id'
        OR chats.seller_id='$user_id'
        ORDER BY chats.id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Chats - Campus Market</title>
<link rel="stylesheet" href="css/style.css">

<style>
body{
    background:#f4f7fc;
}

.wrap{
    width:850px;
    max-width:95%;
    margin:35px auto;
}

.topbar,
.chat-card{
    background:white;
    padding:18px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
    margin-bottom:14px;
}

.chat-card{
    display:flex;
    justify-content:space-between;
    gap:15px;
    align-items:center;
}

.unread{
    display:inline-block;
    padding:5px 9px;
    border-radius:20px;
    background:#dc3545;
    color:white;
    font-size:13px;
}

.btn{
    display:inline-block;
    padding:10px 15px;
    background:#0d6efd;
    color:white;
    text-decoration:none;
    border-radius:6px;
}

@media(max-width:700px){
    .chat-card{
        flex-direction:column;
        align-items:flex-start;
    }
}
</style>
</head>
<body>

<div class="wrap">
    <div class="topbar">
        <h1>My Chats</h1>
        <a class="btn" href="dashboard.php">Dashboard</a>
    </div>

    <?php if(mysqli_num_rows($result) == 0){ ?>
        <div class="chat-card">
            <p>No chats yet.</p>
        </div>
    <?php } ?>

    <?php while($chat = mysqli_fetch_assoc($result)){ ?>
        <div class="chat-card">
            <div>
                <h3><?php echo htmlspecialchars($chat['title']); ?></h3>
                <p>
                    <b>Buyer:</b> <?php echo htmlspecialchars($chat['buyer_name']); ?> |
                    <b>Seller:</b> <?php echo htmlspecialchars($chat['seller_name']); ?>
                </p>

                <p>
                    <?php echo !empty($chat['last_message']) ? htmlspecialchars($chat['last_message']) : "No messages yet."; ?>
                </p>

                <?php if((int)$chat['unread_count'] > 0){ ?>
                    <span class="unread">
                        <?php echo (int)$chat['unread_count']; ?> new
                    </span>
                <?php } ?>
            </div>

            <a
            class="btn"
            href="chat.php?chat_id=<?php echo (int)$chat['id']; ?>">
                Open Chat
            </a>
        </div>
    <?php } ?>
</div>

</body>
</html>
