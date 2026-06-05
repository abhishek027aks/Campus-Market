<?php
include "../backend/config.php";

if(!isset($_GET['id'])){
    die("Product Not Found");
}

$id = (int)$_GET['id'];

$sql = "SELECT products.*, users.fullname, users.email
        FROM products
        JOIN users ON products.seller_id = users.id
        WHERE products.id = $id";

$result = mysqli_query($conn,$sql);

$product = mysqli_fetch_assoc($result);

if(!$product){
    die("Product Not Found");
}

$fileType = strtolower($product['file_type']);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo $product['title']; ?></title>

<link rel="stylesheet" href="css/style.css">

<style>

body{
    background:#f4f7fc;
    font-family:Arial;
}

.product-box{
    width:800px;
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

.preview video{
    width:100%;
    border-radius:10px;
}

iframe{
    width:100%;
    height:600px;
    border:none;
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

</style>

</head>
<body>

<div class="product-box">

<div class="preview">

<?php

if($fileType=="pdf"){

    if(!empty($product['preview_image'])){
        echo '<img src="uploads/'.$product['preview_image'].'">';
    }else{
        echo '<img src="images/pdf.png" width="180">';
    }

    echo '<br><br>';

    echo '<iframe src="uploads/'.$product['image'].'"></iframe>';
}

elseif($fileType=="mp4"){

    echo '
    <video controls>
        <source src="uploads/'.$product['image'].'" type="video/mp4">
    </video>';
}

elseif(
    $fileType=="jpg" ||
    $fileType=="jpeg" ||
    $fileType=="png" ||
    $fileType=="image"
){

    echo '
    <img src="uploads/'.$product['image'].'">';
}

elseif($fileType=="docx"){

    if(!empty($product['preview_image'])){
        echo '<img src="uploads/'.$product['preview_image'].'">';
    }else{
        echo '<img src="images/docx.png" width="180">';
    }
}

elseif($fileType=="pptx"){

    if(!empty($product['preview_image'])){
        echo '<img src="uploads/'.$product['preview_image'].'">';
    }else{
        echo '<img src="images/ppt.png" width="180">';
    }
}

else{

    echo '<img src="images/file.png" width="180">';
}

?>

</div>

<h2><?php echo $product['title']; ?></h2>

<p><?php echo $product['description']; ?></p>

<div class="price">
₹<?php echo $product['price']; ?>
</div>

<div class="info-box">

<p>
<b>Category:</b>
<?php echo $product['category']; ?>
</p>

<p>
<b>Status:</b>
<?php echo $product['status']; ?>
</p>

<p>
<b>File Type:</b>
<?php echo strtoupper($product['file_type']); ?>
</p>

<p>
<b>Uploaded On:</b>
<?php echo date("d M Y",strtotime($product['created_at'])); ?>
</p>

</div>

<div class="info-box">

<h3>Seller Information</h3>

<p>
<b>Name:</b>
<?php echo $product['fullname']; ?>
</p>

<p>
<b>Email:</b>
<?php echo $product['email']; ?>
</p>

</div>

<br>

<a
class="btn download"
href="uploads/<?php echo $product['image']; ?>"
download>
Download File
</a>

<a
class="btn contact"
href="mailto:<?php echo $product['email']; ?>">
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