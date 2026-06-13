<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$notification_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM notifications
         WHERE user_id='$user_id'
         AND is_read=0"
    )
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Campus Market</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="form-container">

    <h1>Welcome <?php echo $_SESSION['fullname']; ?></h1>

    <p>Login Successful ✅</p>

    <br>

    <a href="products.php">
        <button>View Products</button>
    </a>

    <br><br>

    <a href="sell.html">
        <button>Sell Product</button>
    </a>

    <br><br>

    <a href="my-products.php">
        <button>My Products</button>
    </a>

    <br><br>

    <a href="profile.php">
        <button>My Profile</button>
    </a>

    <br><br>

    <a href="my-wishlist.php">
        <button>My Wishlist</button>
    </a>

    <br><br>

    <a href="my-chats.php">
        <button>My Chats</button>
    </a>

    <br><br>

    <a href="notifications.php">
        <button>
            Notifications (<?php echo (int)$notification_count['total']; ?>)
        </button>
    </a>

    <br><br>

    <a href="../backend/logout.php">
        <button>Logout</button>
    </a>

</div>

</body>
</html>
