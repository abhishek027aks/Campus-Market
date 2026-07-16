<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$summary_stmt = mysqli_prepare(
    $conn,
    "SELECT
     COUNT(*) AS products_uploaded,
     COALESCE(SUM(views), 0) AS total_views,
     COALESCE(SUM(downloads), 0) AS total_downloads
     FROM products
     WHERE seller_id=?"
);
mysqli_stmt_bind_param($summary_stmt, "i", $user_id);
mysqli_stmt_execute($summary_stmt);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($summary_stmt));

$wishlist_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total_wishlist
     FROM wishlist
     JOIN products ON wishlist.product_id = products.id
     WHERE products.seller_id=?"
);
mysqli_stmt_bind_param($wishlist_stmt, "i", $user_id);
mysqli_stmt_execute($wishlist_stmt);
$wishlist = mysqli_fetch_assoc(mysqli_stmt_get_result($wishlist_stmt));

$reviews_stmt = mysqli_prepare(
    $conn,
    "SELECT
     COUNT(product_reviews.id) AS total_reviews,
     COALESCE(AVG(product_reviews.rating), 0) AS average_rating
     FROM product_reviews
     JOIN products ON product_reviews.product_id = products.id
     WHERE products.seller_id=?"
);
mysqli_stmt_bind_param($reviews_stmt, "i", $user_id);
mysqli_stmt_execute($reviews_stmt);
$reviews = mysqli_fetch_assoc(mysqli_stmt_get_result($reviews_stmt));

$messages_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(messages.id) AS total_messages
     FROM messages
     JOIN chats ON messages.chat_id = chats.id
     WHERE chats.seller_id=?"
);
mysqli_stmt_bind_param($messages_stmt, "i", $user_id);
mysqli_stmt_execute($messages_stmt);
$messages = mysqli_fetch_assoc(mysqli_stmt_get_result($messages_stmt));

$products_stmt = mysqli_prepare(
    $conn,
    "SELECT products.*,
     COALESCE(wishlist_summary.total_wishlist, 0) AS wishlist_count,
     COALESCE(review_summary.total_reviews, 0) AS total_reviews,
     COALESCE(review_summary.average_rating, 0) AS average_rating
     FROM products
     LEFT JOIN (
        SELECT product_id, COUNT(*) AS total_wishlist
        FROM wishlist
        GROUP BY product_id
     ) AS wishlist_summary ON products.id = wishlist_summary.product_id
     LEFT JOIN (
        SELECT product_id,
        COUNT(*) AS total_reviews,
        AVG(rating) AS average_rating
        FROM product_reviews
        GROUP BY product_id
     ) AS review_summary ON products.id = review_summary.product_id
     WHERE products.seller_id=?
     ORDER BY products.views DESC"
);
mysqli_stmt_bind_param($products_stmt, "i", $user_id);
mysqli_stmt_execute($products_stmt);
$products_result = mysqli_stmt_get_result($products_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Analytics - Campus Market</title>
<link rel="stylesheet" href="css/style.css">

<style>
body{
    background:#f4f7fc;
}

.wrap{
    width:1100px;
    max-width:95%;
    margin:35px auto;
}

.topbar,
.stat-card,
.table-box{
    background:white;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
}

.topbar{
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:18px;
}

.stats{
    display:grid;
    grid-template-columns:repeat(6,1fr);
    gap:15px;
    margin-bottom:18px;
}

.stat-card{
    padding:18px;
}

.stat-card h2{
    color:#0d6efd;
    font-size:30px;
}

.table-box{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,
td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:left;
}

th{
    background:#0d6efd;
    color:white;
}

.btn{
    display:inline-block;
    padding:10px 15px;
    background:#0d6efd;
    color:white;
    text-decoration:none;
    border-radius:6px;
    margin:3px;
}

.qr{
    background:#198754;
}

@media(max-width:900px){
    .stats{
        grid-template-columns:1fr;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
    }
}
</style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="wrap">
    <div class="topbar">
        <div>
            <h1>Seller Analytics</h1>
            <p>Track product performance and buyer interest.</p>
        </div>

        <a class="btn" href="dashboard.php">Dashboard</a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h2><?php echo (int)$summary['products_uploaded']; ?></h2>
            <p>Products Uploaded</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$summary['total_views']; ?></h2>
            <p>Total Views</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$summary['total_downloads']; ?></h2>
            <p>Total Downloads</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$wishlist['total_wishlist']; ?></h2>
            <p>Wishlist Saves</p>
        </div>

        <div class="stat-card">
            <h2><?php echo round((float)$reviews['average_rating'], 1); ?></h2>
            <p>Average Rating</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$messages['total_messages']; ?></h2>
            <p>Messages</p>
        </div>
    </div>

    <div class="table-box">
        <table>
            <tr>
                <th>Product</th>
                <th>Views</th>
                <th>Downloads</th>
                <th>Wishlist</th>
                <th>Rating</th>
                <th>Reviews</th>
                <th>Action</th>
            </tr>

            <?php while($product = mysqli_fetch_assoc($products_result)){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                    <td><?php echo (int)$product['views']; ?></td>
                    <td><?php echo (int)$product['downloads']; ?></td>
                    <td><?php echo (int)$product['wishlist_count']; ?></td>
                    <td><?php echo round((float)$product['average_rating'], 1); ?> / 5</td>
                    <td><?php echo (int)$product['total_reviews']; ?></td>
                    <td>
                        <a
                        class="btn"
                        href="product-details.php?id=<?php echo (int)$product['id']; ?>">
                            View
                        </a>

                        <a
                        class="btn qr"
                        href="product-qr.php?id=<?php echo (int)$product['id']; ?>">
                            QR
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>
