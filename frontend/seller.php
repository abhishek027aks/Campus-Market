<?php
include "../backend/config.php";

if(!isset($_GET['id'])){
    die("Seller Not Found");
}

$seller_id = (int)$_GET['id'];

$seller_sql = "SELECT * FROM users WHERE id='$seller_id'";
$seller_result = mysqli_query($conn, $seller_sql);
$seller = mysqli_fetch_assoc($seller_result);

if(!$seller){
    die("Seller Not Found");
}

$product_sql = "SELECT * FROM products
                WHERE seller_id='$seller_id'
                AND approval_status='Approved'
                ORDER BY id DESC";

$product_result = mysqli_query($conn, $product_sql);

$stats_sql = "SELECT COUNT(*) AS total_products,
              COALESCE(SUM(views), 0) AS total_views
              FROM products
              WHERE seller_id='$seller_id'
              AND approval_status='Approved'";

$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_sql));
$isVerified = $seller['verification_status'] == "Approved";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($seller['fullname']); ?> - Seller Profile</title>
<link rel="stylesheet" href="css/style.css">

<style>
body{
    background:#f4f7fc;
}

.seller-wrap{
    width:1050px;
    max-width:95%;
    margin:35px auto;
}

.seller-header{
    background:white;
    padding:26px;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    display:grid;
    grid-template-columns:150px 1fr;
    gap:22px;
    align-items:center;
}

.seller-photo{
    width:140px;
    height:140px;
    border-radius:50%;
    object-fit:cover;
    background:#e9ecef;
}

.seller-initial{
    width:140px;
    height:140px;
    border-radius:50%;
    background:#0d6efd;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:52px;
    font-weight:bold;
}

.verified{
    display:inline-block;
    padding:7px 12px;
    border-radius:20px;
    color:white;
    background:#198754;
    font-size:13px;
    margin-top:8px;
}

.not-verified{
    display:inline-block;
    padding:7px 12px;
    border-radius:20px;
    color:white;
    background:#6c757d;
    font-size:13px;
    margin-top:8px;
}

.seller-meta{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-top:18px;
}

.meta-card{
    background:#f8f9fa;
    padding:12px;
    border-radius:8px;
}

.section-title{
    margin:28px 0 12px;
}

.features{
    padding:0;
}

.card .views{
    color:#555;
    font-size:14px;
    margin-top:8px;
}

.btn{
    display:inline-block;
    padding:10px 15px;
    background:#0d6efd;
    color:white;
    text-decoration:none;
    border-radius:5px;
    margin-top:10px;
}

@media(max-width:768px){
    .seller-header{
        grid-template-columns:1fr;
        text-align:center;
    }

    .seller-photo,
    .seller-initial{
        margin:auto;
    }

    .seller-meta{
        grid-template-columns:1fr;
        text-align:left;
    }
}
</style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="seller-wrap">
    <div class="seller-header">
        <div>
            <?php if(!empty($seller['profile_photo'])){ ?>
                <img
                class="seller-photo"
                src="uploads/<?php echo htmlspecialchars($seller['profile_photo']); ?>"
                alt="Seller photo">
            <?php }else{ ?>
                <div class="seller-initial">
                    <?php echo strtoupper(substr($seller['fullname'], 0, 1)); ?>
                </div>
            <?php } ?>
        </div>

        <div>
            <h1><?php echo htmlspecialchars($seller['fullname']); ?></h1>
            <p><?php echo htmlspecialchars($seller['email']); ?></p>

            <?php if($isVerified){ ?>
                <span class="verified">Verified Student</span>
            <?php }else{ ?>
                <span class="not-verified">Not Verified</span>
            <?php } ?>

            <div class="seller-meta">
                <div class="meta-card">
                    <strong>Semester</strong><br>
                    <?php echo htmlspecialchars($seller['semester']); ?>
                </div>

                <div class="meta-card">
                    <strong>Course</strong><br>
                    <?php echo htmlspecialchars($seller['course']); ?>
                </div>

                <div class="meta-card">
                    <strong>Products</strong><br>
                    <?php echo (int)$stats['total_products']; ?>
                </div>

                <div class="meta-card">
                    <strong>Views</strong><br>
                    <?php echo (int)$stats['total_views']; ?>
                </div>
            </div>
        </div>
    </div>

    <h2 class="section-title">Seller Products</h2>

    <div class="features">
        <?php while($row = mysqli_fetch_assoc($product_result)){ ?>
            <div class="card">
                <?php if(!empty($row['preview_image'])){ ?>
                    <img
                    class="product-img"
                    src="uploads/<?php echo htmlspecialchars($row['preview_image']); ?>"
                    alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php }else{ ?>
                    <img
                    class="product-img"
                    src="uploads/<?php echo htmlspecialchars($row['image']); ?>"
                    alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php } ?>

                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <div class="price">Rs. <?php echo htmlspecialchars($row['price']); ?></div>
                <p class="views">Views: <?php echo (int)$row['views']; ?></p>

                <a
                class="btn"
                href="product-details.php?id=<?php echo (int)$row['id']; ?>">
                    View Details
                </a>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
