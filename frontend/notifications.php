<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM notifications
     WHERE user_id=?
     ORDER BY id DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications - Campus Market</title>
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
.notification{
    background:white;
    padding:18px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
    margin-bottom:14px;
}

.notification.unread{
    border-left:5px solid #0d6efd;
}

.notification.read{
    opacity:.8;
}

.date{
    color:#666;
    font-size:13px;
    margin-top:6px;
}

.btn{
    display:inline-block;
    padding:10px 15px;
    background:#0d6efd;
    color:white;
    text-decoration:none;
    border-radius:6px;
    margin-top:10px;
}
</style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="wrap">
    <div class="topbar">
        <h1>Notifications</h1>
        <a class="btn" href="dashboard.php">Dashboard</a>
    </div>

    <?php if(mysqli_num_rows($result) == 0){ ?>
        <div class="notification">
            <p>No notifications yet.</p>
        </div>
    <?php } ?>

    <?php while($notification = mysqli_fetch_assoc($result)){ ?>
        <div class="notification <?php echo (int)$notification['is_read'] == 0 ? 'unread' : 'read'; ?>">
            <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
            <p><?php echo htmlspecialchars($notification['message']); ?></p>
            <div class="date">
                <?php echo date("d M Y, h:i A", strtotime($notification['created_at'])); ?>
            </div>

            <a
            class="btn"
            href="../backend/mark-notification.php?id=<?php echo (int)$notification['id']; ?>">
                Open
            </a>
        </div>
    <?php } ?>
</div>

</body>
</html>
