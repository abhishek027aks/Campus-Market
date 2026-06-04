<?php
include "../backend/config.php";

$search = "";

if(isset($_GET['search']) && !empty($_GET['search'])){

    $search = $_GET['search'];

    $sql = "SELECT * FROM products
            WHERE title LIKE '%$search%'
            ORDER BY id DESC";

}else{

    $sql = "SELECT * FROM products
            ORDER BY id DESC";
}

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products - Campus Market</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2 style="text-align:center;">All Products</h2>

<form method="GET" style="text-align:center;margin:20px;">

    <input
        type="text"
        name="search"
        placeholder="Search Product"
        value="<?php echo $search; ?>">

    <button type="submit">
        Search
    </button>

</form>

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

</div>

<?php
}
?>

</div>

</body>
</html>