<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$notification_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM notifications
     WHERE user_id=?
     AND is_read=0"
);
mysqli_stmt_bind_param($notification_stmt, "i", $user_id);
mysqli_stmt_execute($notification_stmt);
$notification_count = mysqli_fetch_assoc(mysqli_stmt_get_result($notification_stmt));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Campus Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body{
            background:#f4f7fc;
            font-family:Arial,sans-serif;
            color:#1f2937;
        }

        .dashboard-wrap{
            width:1120px;
            max-width:94%;
            margin:35px auto;
        }

        .dashboard-header,
        .dash-card{
            background:white;
            border-radius:8px;
            box-shadow:0 0 10px rgba(0,0,0,0.08);
        }

        .dashboard-header{
            padding:22px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
        }

        .dashboard-header h1{
            margin:0;
            font-size:30px;
        }

        .dashboard-header p{
            margin:8px 0 0;
            color:#6b7280;
        }

        .quick-actions{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .dashboard-grid{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:18px;
            margin-top:20px;
        }

        .dash-card{
            padding:20px;
            min-height:210px;
        }

        .dash-card h2{
            margin:0 0 8px;
            font-size:22px;
        }

        .dash-card p{
            margin:0 0 15px;
            color:#6b7280;
            font-size:14px;
        }

        .link-stack{
            display:flex;
            flex-direction:column;
            gap:10px;
        }

        .dash-btn{
            display:block;
            padding:11px 13px;
            border-radius:6px;
            background:#0d6efd;
            color:white;
            text-decoration:none;
            text-align:center;
            font-weight:bold;
        }

        .success{background:#198754}
        .purple{background:#6f42c1}
        .warning{background:#fd7e14;color:#111}
        .danger{background:#dc3545}
        .muted{background:#6c757d}

        .notification-pill{
            display:inline-block;
            padding:4px 8px;
            border-radius:20px;
            background:#ffc107;
            color:#111;
            margin-left:6px;
            font-size:13px;
        }

        @media(max-width:1000px){
            .dashboard-grid{
                grid-template-columns:repeat(2,1fr);
            }
        }

        @media(max-width:650px){
            .dashboard-header{
                flex-direction:column;
                align-items:flex-start;
            }

            .dashboard-grid{
                grid-template-columns:1fr;
            }

            .quick-actions{
                width:100%;
            }

            .quick-actions .dash-btn{
                flex:1;
            }
        }
    </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<main class="dashboard-wrap">
    <section class="dashboard-header">
        <div>
            <h1>Welcome <?php echo htmlspecialchars($_SESSION['fullname']); ?></h1>
            <p>Manage your buying, selling, messages and campus updates.</p>
        </div>

        <div class="quick-actions">
            <a class="dash-btn" href="notifications.php">
                Notifications
                <span class="notification-pill"><?php echo (int)$notification_count['total']; ?></span>
            </a>
            <a class="dash-btn danger" href="../backend/logout.php">Logout</a>
        </div>
    </section>

    <section class="dashboard-grid">
        <div class="dash-card">
            <h2>Marketplace</h2>
            <p>Browse products, saved items and campus utility boards.</p>
            <div class="link-stack">
                <a class="dash-btn" href="products.php">View Products</a>
                <a class="dash-btn purple" href="my-wishlist.php">My Wishlist</a>
                <a class="dash-btn warning" href="lost-found.php">Lost & Found</a>
            </div>
        </div>

        <div class="dash-card">
            <h2>Seller Tools</h2>
            <p>Upload products, manage listings and track performance.</p>
            <div class="link-stack">
                <a class="dash-btn success" href="sell.html">Sell Product</a>
                <a class="dash-btn" href="my-products.php">My Products</a>
                <a class="dash-btn purple" href="seller-analytics.php">Seller Analytics</a>
            </div>
        </div>

        <div class="dash-card">
            <h2>Account</h2>
            <p>Keep your profile, payments and student information updated.</p>
            <div class="link-stack">
                <a class="dash-btn" href="profile.php">My Profile</a>
                <a class="dash-btn success" href="my-payments.php">My Payments</a>
            </div>
        </div>

        <div class="dash-card">
            <h2>Communication</h2>
            <p>Check messages and important college announcements.</p>
            <div class="link-stack">
                <a class="dash-btn warning" href="my-chats.php">My Chats</a>
                <a class="dash-btn muted" href="notice-board.php">Notice Board</a>
                <a class="dash-btn" href="notifications.php">Notifications</a>
            </div>
        </div>
    </section>
</main>

</body>
</html>
