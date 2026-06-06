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
$user_id = (int)$_SESSION['user_id'];

$sql = "SELECT * FROM products
        WHERE id='$id'
        AND seller_id='$user_id'";

$result = mysqli_query($conn, $sql);

if(!$result){
    die("Query Error: " . mysqli_error($conn));
}

$product = mysqli_fetch_assoc($result);

if(!$product){
    die("No Product Found With ID = " . $id);
}

$defaultCategories = ["Books", "Electronics", "Notes", "Accessories"];
$isOtherCategory = !in_array($product['category'], $defaultCategories);
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
    width:500px;
    max-width:95%;
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
.form-container textarea,
.form-container select{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;
}

.form-container label{
    display:block;
    margin-top:12px;
    font-weight:bold;
}

.current-file{
    color:#555;
    font-size:14px;
    margin-bottom:8px;
}

.form-container button{
    width:100%;
    padding:12px;
    border:none;
    background:#007bff;
    color:white;
    border-radius:5px;
    cursor:pointer;
    margin-top:15px;
}

.form-container button:hover{
    background:#0056b3;
}
</style>

</head>
<body>

<div class="form-container">

    <h2>Edit Product</h2>

    <form
        action="../backend/update-product.php"
        method="POST"
        enctype="multipart/form-data">

        <input
            type="hidden"
            name="id"
            value="<?php echo (int)$product['id']; ?>">

        <label>Product Name</label>
        <input
            type="text"
            name="title"
            value="<?php echo htmlspecialchars($product['title']); ?>"
            required>

        <label>Price</label>
        <input
            type="number"
            step="0.01"
            name="price"
            value="<?php echo htmlspecialchars($product['price']); ?>"
            required>

        <label>Description</label>
        <textarea
            name="description"
            rows="5"
            required><?php echo htmlspecialchars($product['description']); ?></textarea>

        <label>Category</label>
        <select
            name="category"
            id="categorySelect"
            required>

            <option value="">Select Category</option>
            <option value="Books" <?php if($product['category']=="Books") echo "selected"; ?>>Books</option>
            <option value="Electronics" <?php if($product['category']=="Electronics") echo "selected"; ?>>Electronics</option>
            <option value="Notes" <?php if($product['category']=="Notes") echo "selected"; ?>>Notes</option>
            <option value="Accessories" <?php if($product['category']=="Accessories") echo "selected"; ?>>Accessories</option>
            <option value="Others" <?php if($isOtherCategory) echo "selected"; ?>>Others</option>

        </select>

        <input
            type="text"
            name="other_category"
            id="otherCategory"
            placeholder="Specify Category"
            value="<?php echo $isOtherCategory ? htmlspecialchars($product['category']) : ''; ?>"
            style="<?php echo $isOtherCategory ? 'display:block;' : 'display:none;'; ?>">

        <label>Status</label>
        <select name="status" required>
            <option value="Available" <?php if($product['status']=="Available") echo "selected"; ?>>Available</option>
            <option value="Sold" <?php if($product['status']=="Sold") echo "selected"; ?>>Sold</option>
        </select>

        <label>Thumbnail</label>
        <div class="current-file">
            Current: <?php echo !empty($product['preview_image']) ? htmlspecialchars($product['preview_image']) : 'No thumbnail'; ?>
        </div>
        <input
            type="file"
            name="preview_image"
            accept="image/*">

        <label>Main File</label>
        <div class="current-file">
            Current: <?php echo htmlspecialchars($product['image']); ?>
        </div>
        <input
            type="file"
            name="file"
            accept=".jpg,.jpeg,.png,.pdf,.docx,.pptx,.mp4">

        <label>Preview File</label>
        <div class="current-file">
            Current: <?php echo !empty($product['preview_file']) ? htmlspecialchars($product['preview_file']) : 'No preview file'; ?>
        </div>
        <input
            type="file"
            name="preview_file"
            accept=".jpg,.jpeg,.png,.pdf,.mp4">

        <button
            type="submit"
            name="update_product">
            Update Product
        </button>

    </form>

</div>

<script>
const categorySelect = document.getElementById('categorySelect');
const otherCategory = document.getElementById('otherCategory');

function toggleOtherCategory(){
    const showOther = categorySelect.value === 'Others';
    otherCategory.style.display = showOther ? 'block' : 'none';
    otherCategory.required = showOther;

    if(!showOther){
        otherCategory.value = '';
    }
}

categorySelect.addEventListener('change', toggleOtherCategory);
toggleOtherCategory();
</script>

</body>
</html>
