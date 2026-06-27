<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$result = mysqli_query(
    $conn,
    "SELECT payments.*, products.title
     FROM payments
     JOIN products ON payments.product_id = products.id
     WHERE payments.buyer_id='$user_id'
     ORDER BY payments.id DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Payments - Campus Market</title>
<link rel="stylesheet" href="css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.page{width:1000px;max-width:95%;margin:30px auto}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
table{width:100%;border-collapse:collapse;background:#fff;box-shadow:0 0 10px rgba(0,0,0,.08);border-radius:8px;overflow:hidden}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left}
th{background:#0d6efd;color:#fff}
.badge{display:inline-block;padding:6px 10px;border-radius:20px;color:#111;background:#ffc107;font-size:13px}
.btn{display:inline-block;padding:10px 14px;border-radius:6px;background:#0d6efd;color:#fff;text-decoration:none}
@media(max-width:800px){table{display:block;overflow-x:auto}}
</style>
</head>
<body>
<main class="page">
    <div class="topbar">
        <h1>My Payments</h1>
        <a class="btn" href="dashboard.php">Dashboard</a>
    </div>

    <table>
        <tr><th>Product</th><th>Amount</th><th>Method</th><th>Transaction ID</th><th>Status</th><th>Date</th></tr>
        <?php if($result && mysqli_num_rows($result) > 0){ ?>
            <?php while($payment = mysqli_fetch_assoc($result)){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($payment['title']); ?></td>
                    <td>Rs. <?php echo htmlspecialchars($payment['amount']); ?></td>
                    <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                    <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                    <td><span class="badge"><?php echo htmlspecialchars($payment['status']); ?></span></td>
                    <td><?php echo date("d M Y", strtotime($payment['created_at'])); ?></td>
                </tr>
            <?php } ?>
        <?php }else{ ?>
            <tr><td colspan="6">No payments submitted yet.</td></tr>
        <?php } ?>
    </table>
</main>
</body>
</html>
