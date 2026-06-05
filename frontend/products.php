<?php
include "../backend/config.php";

$search = "";
$category = "";

$sql = "SELECT * FROM products WHERE 1=1";

if(isset($_GET['search']) && !empty($_GET['search'])){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $sql .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}

if(isset($_GET['category']) && !empty($_GET['category'])){

    $category = mysqli_real_escape_string($conn,$_GET['category']);

    $sql .= " AND category='$category'";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>

<title>Products - Campus Market</title>

<link rel="stylesheet" href="css/style.css">

<style>

body{
    background:#f4f7fc;
    font-family:Arial;
}

h2{
    text-align:center;
    margin-top:20px;
}

.search-box{
    text-align:center;
    margin:20px;
}

.search-box input,
.search-box select{
    padding:10px;
    margin:5px;
}

.search-box button{
    padding:10px 15px;
    cursor:pointer;
}

.features{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:20px;
    margin-top:20px;
}

.card{
    width:280px;
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    text-align:center;
}

.card img{
    width:240px;
    height:170px;
    object-fit:cover;
    border-radius:10px;
}

.file-icon{
    font-size:80px;
    margin:20px 0;
}

.price{
    color:#0d6efd;
    font-size:24px;
    font-weight:bold;
}

.badge{
    display:inline-block;
    padding:5px 10px;
    border-radius:20px;
    color:white;
    font-size:13px;
    margin:5px;
}

.category{
    background:#6f42c1;
}

.available{
    background:#198754;
}

.sold{
    background:#dc3545;
}

.date{
    color:gray;
    font-size:13px;
    margin-top:10px;
}

.btn{
    display:inline-block;
    padding:10px 15px;
    background:#0d6efd;
    color:white;
    text-decoration:none;
    border-radius:5px;
    margin-top:10px;
}

</style>

</head>
<body>

<h2>All Products</h2>

<div class="search-box">

<form method="GET">

    <input
        type="text"
        name="search"
        placeholder="Search Product"
        value="<?php echo $search; ?>">

    <select name="category">

        <option value="">All Categories</option>

        <option value="Books"
        <?php if($category=="Books") echo "selected"; ?>>
        Books
        </option>

        <option value="Electronics"
        <?php if($category=="Electronics") echo "selected"; ?>>
        Electronics
        </option>

        <option value="Notes"
        <?php if($category=="Notes") echo "selected"; ?>>
        Notes
        </option>

        <option value="Accessories"
        <?php if($category=="Accessories") echo "selected"; ?>>
        Accessories
        </option>

        <option value="Others"
        <?php if($category=="Others") echo "selected"; ?>>
        Others
        </option>

    </select>

    <button type="submit">
        Filter
    </button>

</form>

</div>

<div class="features">

<?php
while($row = mysqli_fetch_assoc($result)){
?>

<div class="card">

<?php

$file = strtolower($row['file_type']);

if($file=="jpg" || $file=="jpeg" || $file=="png"){
?>

    <img src="uploads/<?php echo $row['image']; ?>">

<?php
}
elseif($file=="pdf"){
?>

    <div class="file-icon">📕</div>

<?php
}
elseif($file=="docx"){
?>

    <div class="file-icon">📘</div>

<?php
}
elseif($file=="pptx"){
?>

    <div class="file-icon">📙</div>

<?php
}
elseif($file=="mp4"){
?>

    <div class="file-icon">🎥</div>

<?php
}
else{
?>

    <div class="file-icon">📁</div>

<?php
}
?>

    <h3><?php echo $row['title']; ?></h3>

    <p><?php echo $row['description']; ?></p>

    <div class="price">
        ₹<?php echo $row['price']; ?>
    </div>

    <br>

    <span class="badge category">
        <?php echo $row['category']; ?>
    </span>

    <?php
    if($row['status']=="Available"){
        echo '<span class="badge available">Available</span>';
    }else{
        echo '<span class="badge sold">Sold</span>';
    }
    ?>

    <div class="date">
        Added:
        <?php echo date("d M Y",strtotime($row['created_at'])); ?>
    </div>

    <a
    class="btn"
    href="product-details.php?id=<?php echo $row['id']; ?>">
        View Details
    </a>

</div>

<?php
}
?>

</div>

</body>
</html>