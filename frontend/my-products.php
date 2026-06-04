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
</head>
<body>

<h2 style="text-align:center;">My Products</h2>

<div class="features">

<?php
while($row = mysqli_fetch_assoc($result)){
?>

<div class="card">

    <?php if(!empty($row['image'])){ ?>
        <img
        src="uploads/<?php echo $row['image']; ?>"
        width="220"
        style="border-radius:10px;">
    <?php } ?>

    <h3><?php echo $row['title']; ?></h3>

    <p><?php echo $row['description']; ?></p>

    <h4>₹<?php echo $row['price']; ?></h4>

    <br>

    <a href="edit-product.php?id=<?php echo $row['id']; ?>">
        <button>Edit Product</button>
    </a>

    <br><br>

    <a href="../backend/delete-product.php?id=<?php echo $row['id']; ?>">
        <button>Delete Product</button>
    </a>

</div>

<?php
}
?>

</div>

</body>
</html>