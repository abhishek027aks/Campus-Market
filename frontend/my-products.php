<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM products
        WHERE seller_id='$user_id'
        ORDER BY id DESC";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Products - Campus Market</title>

<link rel="stylesheet" href="css/style.css">

<style>

body{
    background:#f4f7fc;
    font-family:Arial;
}

h2{
    text-align:center;
    margin:20px;
}

.features{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:25px;
}

.card{
    width:300px;
    background:#fff;
    padding:20px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 0 10px rgba(0,0,0,.1);
}

.card img{
    width:240px;
    height:180px;
    object-fit:cover;
    border-radius:10px;
}

.file-icon{
    width:120px;
    height:120px;
    object-fit:contain;
}

.btn{
    display:inline-block;
    padding:10px 15px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    margin:5px;
}

.edit-btn{
    background:#0d6efd;
}

.delete-btn{
    background:#dc3545;
}

.preview-btn{
    background:#198754;
}

.download-btn{
    background:#6f42c1;
}

.qr-btn{
    background:#fd7e14;
}

</style>

</head>
<body>

<h2>My Products</h2>

<div class="features">

<?php
while($row = mysqli_fetch_assoc($result)){
?>

<div class="card">

<?php

$fileType = strtolower($row['file_type']);

if(!empty($row['preview_image'])){
?>

<img
src="uploads/<?php echo $row['preview_image']; ?>">

<?php
}
else{

    if($fileType=="pdf"){
        echo '<img class="file-icon" src="images/pdf.png">';
    }

    elseif($fileType=="docx"){
        echo '<img class="file-icon" src="images/docx.png">';
    }

    elseif($fileType=="pptx"){
        echo '<img class="file-icon" src="images/ppt.png">';
    }

    elseif($fileType=="mp4"){
        echo '<img class="file-icon" src="images/video.png">';
    }

    else{
?>

<img
src="uploads/<?php echo $row['image']; ?>">

<?php
    }
}
?>

<h3><?php echo $row['title']; ?></h3>

<p><?php echo $row['description']; ?></p>

<h4>₹<?php echo $row['price']; ?></h4>

<p>
<b>Category:</b>
<?php echo $row['category']; ?>
</p>

<p>
<b>Status:</b>
<?php echo $row['status']; ?>
</p>

<p>
<b>Approval:</b>
<?php echo htmlspecialchars($row['approval_status']); ?>
</p>

<p>
<b>Type:</b>
<?php echo strtoupper($row['file_type']); ?>
</p>

<a
class="btn preview-btn"
target="_blank"
href="uploads/<?php echo $row['image']; ?>">
Preview
</a>

<a
class="btn download-btn"
download
href="uploads/<?php echo $row['image']; ?>">
Download
</a>

<br>

<a
class="btn edit-btn"
href="edit-product.php?id=<?php echo $row['id']; ?>">
Edit Product
</a>

<a
class="btn qr-btn"
href="product-qr.php?id=<?php echo $row['id']; ?>">
QR Code
</a>

<a
class="btn delete-btn"
href="../backend/delete-product.php?id=<?php echo $row['id']; ?>">
Delete Product
</a>

</div>

<?php
}
?>

</div>

</body>
</html>
