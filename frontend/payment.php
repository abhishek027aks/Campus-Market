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
$buyer_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT products.*, users.fullname, users.email
     FROM products
     JOIN users ON products.seller_id = users.id
     WHERE products.id=?
     AND products.approval_status='Approved'"
);
$result = false;
if($stmt){
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
$product = $result ? mysqli_fetch_assoc($result) : null;

if(!$product){
    die("Product Not Found");
}

if((int)$product['seller_id'] === $buyer_id){
    die("Seller cannot pay for own product");
}

$upi_id = "campusmarket@upi";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment - Campus Market</title>
<link rel="stylesheet" href="css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.payment-box{width:620px;max-width:94%;margin:35px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08)}
.summary,.upi-box{background:#f8f9fa;padding:15px;border-radius:8px;margin:14px 0}
.amount{font-size:28px;color:#0d6efd;font-weight:bold}
label{display:block;font-weight:bold;margin-top:12px}
input,select{width:100%;padding:11px;margin-top:7px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box}
.btn{display:inline-block;padding:11px 16px;border-radius:6px;background:#0d6efd;color:#fff;text-decoration:none;border:0;cursor:pointer;margin-top:14px}
.back{background:#6c757d}
.muted{color:#666;font-size:14px}
</style>
</head>
<body>
<?php include "includes/navbar.php"; ?>
<div class="payment-box">
    <h1>Payment</h1>

    <div class="summary">
        <h2><?php echo htmlspecialchars($product['title']); ?></h2>
        <p>Seller: <?php echo htmlspecialchars($product['fullname']); ?></p>
        <div class="amount">Rs. <?php echo htmlspecialchars($product['price']); ?></div>
    </div>

    <div class="upi-box">
        <p><b>UPI ID:</b> <?php echo htmlspecialchars($upi_id); ?></p>
        <p class="muted">Pay using UPI, PhonePe, Paytm or Google Pay. After payment, submit your transaction ID for seller/admin review.</p>
    </div>

    <form action="../backend/add-payment.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

        <label>Payment Method</label>
        <select name="payment_method" required>
            <option value="">Select Method</option>
            <option value="UPI">UPI</option>
            <option value="PhonePe">PhonePe</option>
            <option value="Paytm">Paytm</option>
            <option value="Google Pay">Google Pay</option>
        </select>

        <label>Transaction ID / UTR</label>
        <input type="text" name="transaction_id" placeholder="Enter transaction ID" required>

        <button class="btn" type="submit" name="submit_payment">Submit Payment</button>
        <a class="btn back" href="product-details.php?id=<?php echo (int)$product['id']; ?>">Back</a>
    </form>
</div>
</body>
</html>
