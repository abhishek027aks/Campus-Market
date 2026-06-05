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

$result = mysqli_query($conn, $sql);

if(!$result){
    die("Query Error: " . mysqli_error($conn));
}

$product = mysqli_fetch_assoc($result);

if(!$product){
    die("Product Not Found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo $product['title']; ?> - Campus Market</title>

<link rel="stylesheet" href="css/style.css">

<style>

body{
    background:#f4f7fc;
    font-family:Arial, sans-serif;
}

.product-box{
    width:550px;
    margin:50px auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    text-align:center;
}

.product-box img{
    width:320px;
    max-width:100%;
    border-radius:10px;
    margin-bottom:15px;
}

.product-box h2{
    margin-bottom:10px;
}

.product-box p{
    color:#555;
}

.price{
    color:#0d6efd;
    font-size:28px;
    font-weight:bold;
}

.seller{
    margin-top:15px;
    padding:10px;
    background:#f8f9fa;
    border-radius:8px;
}

.btn{
    display:inline-block;
    padding:12px 20px;
    margin:10px 5px;
    border-radius:6px;
    text-decoration:none;
    color:white;
}

.contact-btn{
    background:#28a745;
}

.back-btn{
    background:#0d6efd;
}

</style>

</head>
<body>

<div class="product-box">

    <?php if(!empty($product['image'])){ ?>

        <img src="uploads/<?php echo $product['image']; ?>">

    <?php } ?>

    <h2><?php echo $product['title']; ?></h2>

    <p><?php echo $product['description']; ?></p>

    <div class="price">
        ₹<?php echo $product['price']; ?>
    </div>

    <div class="seller">

        <h3>Seller Information</h3>

        <p>
            <strong>Name:</strong>
            <?php echo $product['fullname']; ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?php echo $product['email']; ?>
        </p>

    </div>

    <a
    href="mailto:<?php echo $product['email']; ?>"
    class="btn contact-btn">
        Contact Seller
    </a>

    <a
    href="products.php"
    class="btn back-btn">
        Back To Products
    </a>

</div>

</body>
</html>