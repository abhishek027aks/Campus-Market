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
    "SELECT products.*, wishlist.created_at AS saved_at
     FROM wishlist
     JOIN products ON wishlist.product_id = products.id
     WHERE wishlist.user_id=?
     AND products.approval_status='Approved'
     ORDER BY wishlist.id DESC"
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
<title>My Wishlist - Campus Market</title>
<link rel="stylesheet" href="css/style.css">

<style>
body{
    background:#f4f7fc;
    font-family:Arial;
}

.page-head{
    text-align:center;
    margin:25px 0 10px;
}

.empty{
    width:500px;
    max-width:95%;
    margin:30px auto;
    background:white;
    padding:25px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.btn{
    display:inline-block;
    padding:10px 15px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    margin:5px;
}

.details{
    background:#0d6efd;
}

.remove{
    background:#dc3545;
}

.back{
    background:#6f42c1;
}

.saved{
    color:#555;
    font-size:13px;
}
</style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="page-head">
    <h1>My Wishlist</h1>
    <a class="btn back" href="dashboard.php">Dashboard</a>
</div>

<?php if(mysqli_num_rows($result) == 0){ ?>
    <div class="empty">
        <h2>No wishlist items yet.</h2>
        <p>Products page se items save kar sakte ho.</p>
        <a class="btn details" href="products.php">Browse Products</a>
    </div>
<?php }else{ ?>
    <div class="features">
        <?php while($row = mysqli_fetch_assoc($result)){ ?>
            <div class="card">
                <?php
                $fileType = strtolower($row['file_type']);

                if(!empty($row['preview_image'])){
                ?>
                    <img
                    class="product-img"
                    src="uploads/<?php echo htmlspecialchars($row['preview_image']); ?>"
                    alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php
                }
                elseif($fileType=="pdf"){
                    echo '<img class="product-img" src="images/pdf.png" alt="PDF file">';
                }
                elseif($fileType=="docx"){
                    echo '<img class="product-img" src="images/docx.png" alt="DOCX file">';
                }
                elseif($fileType=="pptx"){
                    echo '<img class="product-img" src="images/ppt.png" alt="PPTX file">';
                }
                elseif($fileType=="mp4"){
                    echo '<img class="product-img" src="images/video.png" alt="Video file">';
                }
                else{
                ?>
                    <img
                    class="product-img"
                    src="uploads/<?php echo htmlspecialchars($row['image']); ?>"
                    alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php } ?>

                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <div class="price">Rs. <?php echo htmlspecialchars($row['price']); ?></div>
                <p class="saved">Saved: <?php echo date("d M Y", strtotime($row['saved_at'])); ?></p>
                <p>Views: <?php echo (int)$row['views']; ?></p>

                <a
                class="btn details"
                href="product-details.php?id=<?php echo (int)$row['id']; ?>">
                    View Details
                </a>

                <a
                class="btn remove"
                href="../backend/toggle-wishlist.php?product_id=<?php echo (int)$row['id']; ?>&redirect=wishlist">
                    Remove
                </a>
            </div>
        <?php } ?>
    </div>
<?php } ?>

</body>
</html>
