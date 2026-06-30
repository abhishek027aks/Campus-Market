<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

if(!isset($_GET['product_id'])){
    die("Product Not Found");
}

$product_id = (int)$_GET['product_id'];
$user_id = (int)$_SESSION['user_id'];

$result = mysqli_query(
    $conn,
    "SELECT products.*, users.fullname
     FROM products
     JOIN users ON products.seller_id = users.id
     WHERE products.id='$product_id'
     AND products.approval_status='Approved'"
);
$product = $result ? mysqli_fetch_assoc($result) : null;

if(!$product){
    die("Product Not Found");
}

if((int)$product['seller_id'] === $user_id){
    die("Seller cannot report own product");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Product - Campus Market</title>
<link rel="stylesheet" href="css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.form-box{width:600px;max-width:94%;margin:35px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08)}
.summary{background:#f8f9fa;padding:14px;border-radius:8px;margin:14px 0}
label{display:block;font-weight:bold;margin-top:12px}
select,textarea{width:100%;padding:11px;margin-top:7px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box}
textarea{min-height:130px;resize:vertical}
.btn{display:inline-block;padding:11px 16px;border-radius:6px;background:#dc3545;color:#fff;text-decoration:none;border:0;cursor:pointer;margin-top:14px}
.back{background:#6c757d}
.muted{color:#666;font-size:14px}
</style>
</head>
<body>
<?php include "includes/navbar.php"; ?>
<div class="form-box">
    <h1>Report Product</h1>

    <div class="summary">
        <h2><?php echo htmlspecialchars($product['title']); ?></h2>
        <p class="muted">Seller: <?php echo htmlspecialchars($product['fullname']); ?></p>
    </div>

    <form action="../backend/add-report.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

        <label>Reason</label>
        <select name="reason" required>
            <option value="">Select Reason</option>
            <option value="Fake Product">Fake Product</option>
            <option value="Wrong Information">Wrong Information</option>
            <option value="Inappropriate Content">Inappropriate Content</option>
            <option value="Duplicate Product">Duplicate Product</option>
            <option value="Other">Other</option>
        </select>

        <label>Description</label>
        <textarea name="description" placeholder="Explain the issue clearly" required></textarea>

        <button class="btn" type="submit" name="submit_report">Submit Report</button>
        <a class="btn back" href="product-details.php?id=<?php echo (int)$product['id']; ?>">Back</a>
    </form>
</div>
</body>
</html>
