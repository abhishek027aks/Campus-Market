<?php
session_start();
include "../backend/config.php";

if(!isset($_GET['id'])){
    die("Product Not Found");
}

$id = (int)$_GET['id'];

$visibility = "products.approval_status='Approved'";

if(isset($_SESSION['user_id'])){
    $viewer_id = (int)$_SESSION['user_id'];
    $visibility .= " OR products.seller_id='$viewer_id'";
}

if(isset($_SESSION['admin_id'])){
    $visibility .= " OR 1=1";
}

$sql = "SELECT products.*, users.fullname, users.email, users.verification_status
        FROM products
        JOIN users ON products.seller_id = users.id
        WHERE products.id = $id
        AND ($visibility)";

$result = mysqli_query($conn,$sql);
$product = mysqli_fetch_assoc($result);

if(!$product){
    die("Product Not Found");
}

if($product['approval_status'] == "Approved"){
    mysqli_query(
        $conn,
        "UPDATE products SET views = views + 1 WHERE id = $id"
    );
}

$fileType = strtolower($product['file_type']);
$previewFile = !empty($product['preview_file']) ? $product['preview_file'] : $product['image'];
$previewExt = strtolower(pathinfo($previewFile, PATHINFO_EXTENSION));
$isWishlisted = false;

if(isset($_SESSION['user_id'])){
    $user_id = (int)$_SESSION['user_id'];
    $wishlist_sql = "SELECT id FROM wishlist
                     WHERE user_id='$user_id'
                     AND product_id='$id'";
    $wishlist_result = mysqli_query($conn, $wishlist_sql);
    $isWishlisted = $wishlist_result && mysqli_num_rows($wishlist_result) > 0;
}

$review_stats_sql = "SELECT
                     COUNT(*) AS total_reviews,
                     COALESCE(AVG(rating), 0) AS average_rating
                     FROM product_reviews
                     WHERE product_id='$id'";

$review_stats = mysqli_fetch_assoc(mysqli_query($conn, $review_stats_sql));
$averageRating = round((float)$review_stats['average_rating'], 1);
$totalReviews = (int)$review_stats['total_reviews'];

$reviews_sql = "SELECT product_reviews.*, users.fullname
                FROM product_reviews
                JOIN users ON product_reviews.user_id = users.id
                WHERE product_reviews.product_id='$id'
                ORDER BY product_reviews.id DESC";

$reviews_result = mysqli_query($conn, $reviews_sql);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($product['title']); ?></title>

<link rel="stylesheet" href="css/style.css">

<style>
body{
    background:#f4f7fc;
    font-family:Arial;
}

.product-box{
    width:800px;
    max-width:95%;
    margin:40px auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,.1);
}

.preview{
    text-align:center;
    margin-bottom:20px;
}

.preview img{
    width:400px;
    max-width:100%;
    border-radius:10px;
}

.price{
    color:#0d6efd;
    font-size:30px;
    font-weight:bold;
}

.info-box{
    background:#f8f9fa;
    padding:15px;
    border-radius:10px;
    margin-top:15px;
}

iframe{
    width:100%;
    height:500px;
    border:none;
    border-radius:10px;
}

.btn{
    display:inline-block;
    padding:12px 18px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    margin:5px;
}

.download{
    background:#6f42c1;
}

.contact{
    background:#198754;
}

.back{
    background:#0d6efd;
}

.wishlist{
    background:#dc3545;
}

.chat{
    background:#fd7e14;
}

.pay{
    background:#20c997;
}

.report{
    background:#dc3545;
}

.success{
    background:#d1e7dd;
    color:#0f5132;
    padding:12px;
    border-radius:8px;
    margin:12px 0;
}

.seller-link{
    color:#0d6efd;
    font-weight:bold;
    text-decoration:none;
}

.verified-badge{
    display:inline-block;
    padding:5px 10px;
    border-radius:20px;
    background:#198754;
    color:white;
    font-size:13px;
}

.featured-badge{
    display:inline-block;
    padding:6px 12px;
    border-radius:20px;
    background:#f59f00;
    color:#111;
    font-size:14px;
    font-weight:bold;
    margin-bottom:10px;
}

.rating-summary{
    display:flex;
    gap:12px;
    align-items:center;
    flex-wrap:wrap;
    margin:10px 0;
}

.stars{
    color:#ffc107;
    font-size:22px;
    letter-spacing:1px;
}

.review-form label{
    display:block;
    font-weight:bold;
    margin-top:12px;
}

.review-form select,
.review-form textarea{
    width:100%;
    padding:12px;
    margin-top:8px;
    border:1px solid #ddd;
    border-radius:6px;
}

.review-form textarea{
    min-height:100px;
    resize:vertical;
}

.review{
    background:white;
    border:1px solid #e5e7eb;
    border-radius:8px;
    padding:14px;
    margin-top:12px;
}

.review-head{
    display:flex;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
}

.review-date{
    color:#666;
    font-size:13px;
}
</style>

</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="product-box">

<?php if(isset($_GET['reported'])){ ?>
    <div class="success">Report submitted successfully. Admin will review it.</div>
<?php } ?>

<div class="preview">

<?php
if(!empty($product['preview_image'])){
    echo '<img src="uploads/'.htmlspecialchars($product['preview_image']).'" alt="'.htmlspecialchars($product['title']).'">';
}
elseif(
    $fileType=="jpg" ||
    $fileType=="jpeg" ||
    $fileType=="png" ||
    $fileType=="image"
){
    echo '<img src="uploads/'.htmlspecialchars($product['image']).'" alt="'.htmlspecialchars($product['title']).'">';
}
elseif($fileType=="pdf"){
    echo '<img src="images/pdf.png" width="180" alt="PDF file">';
}
elseif($fileType=="docx"){
    echo '<img src="images/docx.png" width="180" alt="DOCX file">';
}
elseif($fileType=="pptx"){
    echo '<img src="images/ppt.png" width="180" alt="PPTX file">';
}
elseif($fileType=="mp4"){
    echo '<img src="images/video.png" width="180" alt="Video file">';
}
else{
    echo '<img src="images/file.png" width="180" alt="File">';
}
?>

</div>

<h2><?php echo htmlspecialchars($product['title']); ?></h2>

<?php if((int)$product['is_featured'] === 1){ ?>
    <span class="featured-badge">Featured Product</span>
<?php } ?>

<p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

<div class="price">
Rs. <?php echo htmlspecialchars($product['price']); ?>
</div>

<div class="info-box">

<p><b>Category:</b> <?php echo htmlspecialchars($product['category']); ?></p>

<p><b>Status:</b> <?php echo htmlspecialchars($product['status']); ?></p>

<p><b>File Type:</b> <?php echo htmlspecialchars(strtoupper($product['file_type'])); ?></p>

<p><b>Views:</b> <?php echo (int)$product['views']; ?></p>

<p><b>Downloads:</b> <?php echo (int)$product['downloads']; ?></p>

<p>
<b>Uploaded On:</b>
<?php echo date("d M Y",strtotime($product['created_at'])); ?>
</p>

</div>

<div class="info-box">

<h3>Seller Information</h3>

<p>
<b>Name:</b>
<a
class="seller-link"
href="seller.php?id=<?php echo (int)$product['seller_id']; ?>">
<?php echo htmlspecialchars($product['fullname']); ?>
</a>

<?php if($product['verification_status'] == "Approved"){ ?>
    <span class="verified-badge">Verified</span>
<?php } ?>
</p>

<p><b>Email:</b> <?php echo htmlspecialchars($product['email']); ?></p>

</div>

<div class="info-box">

<h3>Preview Before Purchase</h3>

<?php
if(
    $previewExt=="jpg" ||
    $previewExt=="jpeg" ||
    $previewExt=="png"
){
?>

<img
src="uploads/<?php echo htmlspecialchars($previewFile); ?>"
alt="Product preview"
style="
width:100%;
max-width:700px;
border-radius:10px;
">

<?php
}
elseif($previewExt=="pdf"){
?>

<iframe
src="uploads/<?php echo htmlspecialchars($previewFile); ?>">
</iframe>

<?php
}
elseif($previewExt=="mp4"){
?>

<video
controls
width="100%"
style="border-radius:10px;">

<source
src="uploads/<?php echo htmlspecialchars($previewFile); ?>"
type="video/mp4">

</video>

<?php
}
else{
?>

<p>Preview format not supported for this file. Contact the seller for more details.</p>

<?php
}
?>

</div>

<br>

<?php if(isset($_SESSION['user_id'])){ ?>
<a
class="btn wishlist"
href="../backend/toggle-wishlist.php?product_id=<?php echo (int)$product['id']; ?>">
<?php echo $isWishlisted ? "Remove Wishlist" : "Add To Wishlist"; ?>
</a>

<?php if((int)$_SESSION['user_id'] != (int)$product['seller_id']){ ?>
<a
class="btn chat"
href="chat.php?product_id=<?php echo (int)$product['id']; ?>">
Chat With Seller
</a>

<a
class="btn pay"
href="payment.php?product_id=<?php echo (int)$product['id']; ?>">
Pay Now
</a>

<a
class="btn report"
href="report-product.php?product_id=<?php echo (int)$product['id']; ?>">
Report Product
</a>
<?php } ?>
<?php }else{ ?>
<a
class="btn wishlist"
href="login.html">
Login To Wishlist
</a>

<a
class="btn chat"
href="login.html">
Login To Chat
</a>

<a
class="btn pay"
href="login.html">
Login To Pay
</a>

<a
class="btn report"
href="login.html">
Login To Report
</a>
<?php } ?>

<a
class="btn download"
href="../backend/download-product.php?id=<?php echo (int)$product['id']; ?>"
download>
Download File
</a>

<a
class="btn contact"
href="mailto:<?php echo htmlspecialchars($product['email']); ?>">
Contact Seller
</a>

<a
class="btn back"
href="products.php">
Back To Products
</a>

<div class="info-box" id="reviews">
    <h3>Ratings & Comments</h3>

    <div class="rating-summary">
        <span class="stars">
            <?php
            for($i = 1; $i <= 5; $i++){
                echo $i <= round($averageRating) ? "&#9733;" : "&#9734;";
            }
            ?>
        </span>
        <strong><?php echo $averageRating; ?> / 5</strong>
        <span>(<?php echo $totalReviews; ?> reviews)</span>
    </div>

    <?php if(isset($_SESSION['user_id'])){ ?>
        <form
        class="review-form"
        action="../backend/add-review.php"
        method="POST">

            <input
            type="hidden"
            name="product_id"
            value="<?php echo (int)$product['id']; ?>">

            <label>Rating</label>
            <select name="rating" required>
                <option value="">Select Rating</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Very Good</option>
                <option value="3">3 - Good</option>
                <option value="2">2 - Average</option>
                <option value="1">1 - Poor</option>
            </select>

            <label>Comment</label>
            <textarea
            name="comment"
            placeholder="Write your review"
            required></textarea>

            <button
            type="submit"
            name="submit_review"
            class="btn back">
                Submit Review
            </button>
        </form>
    <?php }else{ ?>
        <p>
            <a href="login.html">Login</a>
            to add rating and comment.
        </p>
    <?php } ?>

    <?php if($totalReviews == 0){ ?>
        <p>No reviews yet.</p>
    <?php }else{ ?>
        <?php while($review = mysqli_fetch_assoc($reviews_result)){ ?>
            <div class="review">
                <div class="review-head">
                    <strong><?php echo htmlspecialchars($review['fullname']); ?></strong>
                    <span class="review-date">
                        <?php echo date("d M Y", strtotime($review['updated_at'])); ?>
                    </span>
                </div>

                <div class="stars">
                    <?php
                    for($i = 1; $i <= 5; $i++){
                        echo $i <= (int)$review['rating'] ? "&#9733;" : "&#9734;";
                    }
                    ?>
                </div>

                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
            </div>
        <?php } ?>
    <?php } ?>
</div>

</div>

</body>
</html>
