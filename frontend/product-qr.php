<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

if(!isset($_GET['id'])){
    die("Product Missing");
}

$id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

$sql = "SELECT * FROM products
        WHERE id='$id'
        AND seller_id='$user_id'";

$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if(!$product){
    die("Product Not Found");
}

$scheme = "http";

if(
    isset($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] == "on"
){
    $scheme = "https";
}

$host = $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$productUrl = $scheme."://".$host.$basePath."/product-details.php?id=".$id;
$qrImage = "https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=".urlencode($productUrl);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Product QR - Campus Market</title>
<link rel="stylesheet" href="css/style.css">

<style>
body{
    background:#f4f7fc;
}

.qr-card{
    width:520px;
    max-width:95%;
    margin:45px auto;
    background:white;
    padding:28px;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    text-align:center;
}

.qr-card img{
    width:260px;
    height:260px;
    margin:20px 0;
}

.url-box{
    word-break:break-all;
    background:#f8f9fa;
    padding:12px;
    border-radius:8px;
    margin:15px 0;
    text-align:left;
}

.btn{
    display:inline-block;
    padding:10px 15px;
    background:#0d6efd;
    color:white;
    text-decoration:none;
    border-radius:6px;
    margin:5px;
    border:none;
    cursor:pointer;
}

.print{
    background:#198754;
}

.back{
    background:#6f42c1;
}
</style>
</head>
<body>

<div class="qr-card">
    <h1>Product QR Code</h1>
    <h3><?php echo htmlspecialchars($product['title']); ?></h3>

    <img
    src="<?php echo htmlspecialchars($qrImage); ?>"
    alt="Product QR Code">

    <div class="url-box">
        <?php echo htmlspecialchars($productUrl); ?>
    </div>

    <button class="btn print" onclick="window.print();">
        Print QR
    </button>

    <a
    class="btn"
    target="_blank"
    href="<?php echo htmlspecialchars($qrImage); ?>">
        Open QR Image
    </a>

    <a
    class="btn back"
    href="my-products.php">
        My Products
    </a>
</div>

</body>
</html>
