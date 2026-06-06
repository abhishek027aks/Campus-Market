<?php
session_start();
include "../backend/config.php";

if(!isset($_GET['id'])){
    die("Product Not Found");
}

$id = (int)$_GET['id'];

mysqli_query(
    $conn,
    "UPDATE products
     SET views = views + 1
     WHERE id = $id"
);

$sql = "SELECT products.*, users.fullname, users.email, users.verification_status
        FROM products
        JOIN users ON products.seller_id = users.id
        WHERE products.id = $id";

$result = mysqli_query($conn,$sql);
$product = mysqli_fetch_assoc($result);

if(!$product){
    die("Product Not Found");
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
</style>

</head>
<body>

<div class="product-box">

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

<p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

<div class="price">
Rs. <?php echo htmlspecialchars($product['price']); ?>
</div>

<div class="info-box">

<p><b>Category:</b> <?php echo htmlspecialchars($product['category']); ?></p>

<p><b>Status:</b> <?php echo htmlspecialchars($product['status']); ?></p>

<p><b>File Type:</b> <?php echo htmlspecialchars(strtoupper($product['file_type'])); ?></p>

<p><b>Views:</b> <?php echo (int)$product['views']; ?></p>

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
<?php }else{ ?>
<a
class="btn wishlist"
href="login.html">
Login To Wishlist
</a>
<?php } ?>

<a
class="btn download"
href="uploads/<?php echo htmlspecialchars($product['image']); ?>"
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

</div>

</body>
</html>
