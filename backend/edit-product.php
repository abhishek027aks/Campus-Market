<?php

session_start();
include "../backend/config.php";

if(!isset($_GET['id'])){
    die("Product Not Found");
}

$id = $_GET['id'];

$sql = "SELECT * FROM products WHERE id='$id'";
$result = mysqli_query($conn,$sql);

$product = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="form-container">

    <h2>Edit Product</h2>

    <form action="../backend/update-product.php" method="POST">

        <input type="hidden"
               name="id"
               value="<?php echo $product['id']; ?>">

        <input type="text"
               name="title"
               value="<?php echo $product['title']; ?>"
               required>

        <input type="number"
               name="price"
               value="<?php echo $product['price']; ?>"
               required>

        <textarea name="description"><?php echo $product['description']; ?></textarea>

        <button type="submit" name="update_product">
            Update Product
        </button>

    </form>

</div>

</body>
</html>