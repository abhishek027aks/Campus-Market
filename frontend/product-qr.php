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

.qr-code{
    width:260px;
    height:260px;
    margin:20px auto;
}

.qr-code img,
.qr-code canvas{
    display:block;
    width:260px;
    height:260px;
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

@media print{
    .qr-actions{
        display:none;
    }

    .qr-card{
        box-shadow:none;
        margin:0 auto;
    }
}
</style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="qr-card">
    <h1>Product QR Code</h1>
    <h3><?php echo htmlspecialchars($product['title']); ?></h3>

    <div id="productQr" class="qr-code" aria-label="Product QR Code"></div>

    <div class="url-box">
        <?php echo htmlspecialchars($productUrl); ?>
    </div>

    <div class="qr-actions">
        <button class="btn" id="downloadQr" type="button">
            Download PNG
        </button>

        <button class="btn print" type="button" onclick="window.print();">
            Print QR
        </button>

        <a class="btn back" href="my-products.php">
            My Products
        </a>
    </div>
</div>

<script src="js/qrcode.min.js"></script>
<script>
const productUrl = <?php echo json_encode($productUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const productTitle = <?php echo json_encode($product['title'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const qrContainer = document.getElementById("productQr");

new QRCode(qrContainer, {
    text: productUrl,
    width: 260,
    height: 260,
    colorDark: "#111827",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
});

document.getElementById("downloadQr").addEventListener("click", function(){
    const canvas = qrContainer.querySelector("canvas");
    const image = qrContainer.querySelector("img");
    const link = document.createElement("a");
    const safeTitle = productTitle.replace(/[^a-z0-9]+/gi, "-").replace(/^-|-$/g, "") || "product";

    link.download = safeTitle + "-qr.png";
    link.href = canvas ? canvas.toDataURL("image/png") : image.src;
    link.click();
});
</script>
</body>
</html>
