<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

if(!isset($_GET['id'])){
    die("Product ID Missing");
}

$id = (int)$_GET['id'];

$sql = "SELECT * FROM products WHERE id='$id'";
$result = mysqli_query($conn, $sql);

if(!$result){
    die("Query Error: " . mysqli_error($conn));
}

$product = mysqli_fetch_assoc($result);

if(!$product){
    die("No Product Found With ID = " . $id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product - Campus Market</title>

<link rel="stylesheet" href="css/style.css">

<style>
.form-container{
    width:400px;
    margin:50px auto;
    padding:20px;
    background:#fff;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.form-container h2{
    text-align:center;
    margin-bottom:20px;
}

.form-container input,
.form-container textarea{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;
}

.form-container button{
    width:100%;
    padding:12px;
    border:none;
    background:#007bff;
    color:white;
    border-radius:5px;
    cursor:pointer;
}

.form-container button:hover{
    background:#0056b3;
}
</style>

</head>
<body>

<div class="form-container">

    <h2>Edit Product</h2>

    <form action="../backend/update-product.php" method="POST">

        <input
            type="hidden"
            name="id"
            value="<?php echo $product['id']; ?>">

        <input
            type="text"
            name="title"
            value="<?php echo htmlspecialchars($product['title']); ?>"
            required>

        <input
            type="number"
            step="0.01"
            name="price"
            value="<?php echo $product['price']; ?>"
            required>

        <textarea
            name="description"
            rows="5"
            required><?php echo htmlspecialchars($product['description']); ?></textarea>

        <button
            type="submit"
            name="update_product">
            Update Product
        </button>

    </form>

</div>

</body>
</html>