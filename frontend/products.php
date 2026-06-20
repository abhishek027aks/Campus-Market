<?php
include "../backend/config.php";

$search = "";
$category = "";

$sql = "SELECT products.*,
        COALESCE(review_summary.average_rating, 0) AS average_rating,
        COALESCE(review_summary.total_reviews, 0) AS total_reviews
        FROM products
        LEFT JOIN (
            SELECT product_id,
            AVG(rating) AS average_rating,
            COUNT(*) AS total_reviews
            FROM product_reviews
            GROUP BY product_id
        ) AS review_summary ON products.id = review_summary.product_id
        WHERE products.approval_status='Approved'";

if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $sql .= " AND (
        products.title LIKE '%$search%'
        OR products.description LIKE '%$search%'
    )";
}

if(isset($_GET['category']) && !empty($_GET['category'])){
    $category = mysqli_real_escape_string($conn,$_GET['category']);
    $sql .= " AND products.category='$category'";
}

$sql .= " ORDER BY products.id DESC";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    box-shadow:0 0 10px rgba(0,0,0,.1);
    text-align:center;
}

.product-img{
    width:240px;
    height:170px;
    object-fit:cover;
    border-radius:10px;
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

.views{
    color:#555;
    font-size:14px;
    margin-top:8px;
}

.rating{
    color:#555;
    font-size:14px;
    margin-top:8px;
}

.stars{
    color:#ffc107;
    letter-spacing:1px;
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
value="<?php echo htmlspecialchars($search); ?>">

<select name="category">

<option value="">All Categories</option>

<option value="Books" <?php if($category=="Books") echo "selected"; ?>>
Books
</option>

<option value="Electronics" <?php if($category=="Electronics") echo "selected"; ?>>
Electronics
</option>

<option value="Notes" <?php if($category=="Notes") echo "selected"; ?>>
Notes
</option>

<option value="Accessories" <?php if($category=="Accessories") echo "selected"; ?>>
Accessories
</option>

<option value="Others" <?php if($category=="Others") echo "selected"; ?>>
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
    $file = strtolower($row['file_type']);
?>

<div class="card">

<?php
if(!empty($row['preview_image'])){
?>

<img
src="uploads/<?php echo htmlspecialchars($row['preview_image']); ?>"
alt="<?php echo htmlspecialchars($row['title']); ?>"
class="product-img">

<?php
}
elseif(
    $file=="image" ||
    $file=="jpg" ||
    $file=="jpeg" ||
    $file=="png"
){
?>

<img
src="uploads/<?php echo htmlspecialchars($row['image']); ?>"
alt="<?php echo htmlspecialchars($row['title']); ?>"
class="product-img">

<?php
}
elseif($file=="pdf"){
?>

<img
src="images/pdf.png"
alt="PDF file"
class="product-img">

<?php
}
elseif($file=="docx"){
?>

<img
src="images/docx.png"
alt="DOCX file"
class="product-img">

<?php
}
elseif($file=="pptx"){
?>

<img
src="images/ppt.png"
alt="PPTX file"
class="product-img">

<?php
}
elseif($file=="mp4"){
?>

<img
src="images/video.png"
alt="Video file"
class="product-img">

<?php
}
else{
?>

<img
src="images/file.png"
alt="File"
class="product-img">

<?php
}
?>

<h3><?php echo htmlspecialchars($row['title']); ?></h3>

<p><?php echo htmlspecialchars($row['description']); ?></p>

<div class="price">
Rs. <?php echo htmlspecialchars($row['price']); ?>
</div>

<br>

<span class="badge category">
<?php echo htmlspecialchars($row['category']); ?>
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

<div class="views">
Views: <?php echo (int)$row['views']; ?>
</div>

<div class="rating">
<span class="stars">
<?php
$cardRating = round((float)$row['average_rating'], 1);

for($i = 1; $i <= 5; $i++){
    echo $i <= round($cardRating) ? "&#9733;" : "&#9734;";
}
?>
</span>
<?php echo $cardRating; ?> / 5
(<?php echo (int)$row['total_reviews']; ?>)
</div>

<a
class="btn"
href="product-details.php?id=<?php echo (int)$row['id']; ?>">
View Details
</a>

</div>

<?php
}
?>

</div>

</body>
</html>
