<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$allowed_statuses = ["Pending", "Approved", "Rejected"];
$status = isset($_GET['status']) ? $_GET['status'] : "";

if($status !== "" && !in_array($status, $allowed_statuses, true)){
    $status = "";
}

$sql = "SELECT payments.*,
        products.title,
        buyer.fullname AS buyer_name,
        buyer.email AS buyer_email,
        seller.fullname AS seller_name,
        seller.email AS seller_email
        FROM payments
        JOIN products ON payments.product_id = products.id
        JOIN users AS buyer ON payments.buyer_id = buyer.id
        JOIN users AS seller ON payments.seller_id = seller.id";

if($status !== ""){
    $safe_status = mysqli_real_escape_string($conn, $status);
    $sql .= " WHERE payments.status='$safe_status'";
}

$sql .= " ORDER BY payments.id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Payments - Admin</title>
<link rel="stylesheet" href="../frontend/css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.admin-wrap{width:1200px;max-width:96%;margin:30px auto}
.topbar,.filters{background:#fff;padding:18px;margin-bottom:18px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08)}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:12px}
.filters select,.filters button{padding:10px;margin-right:8px}
table{width:100%;border-collapse:collapse;background:#fff;box-shadow:0 0 10px rgba(0,0,0,.08)}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
th{background:#0d6efd;color:#fff}
.badge{display:inline-block;padding:6px 10px;border-radius:16px;color:#fff;font-size:13px}
.Pending{background:#ffc107;color:#111}.Approved{background:#198754}.Rejected{background:#dc3545}
.btn{display:inline-block;padding:8px 10px;border:0;border-radius:5px;color:#fff;text-decoration:none;cursor:pointer;margin:2px}
.approve{background:#198754}.reject{background:#dc3545}.view{background:#6f42c1}.back{background:#0d6efd}
.action-form{display:inline}
@media(max-width:900px){table{display:block;overflow-x:auto}.topbar{align-items:flex-start;flex-direction:column}}
</style>
</head>
<body>
<main class="admin-wrap">
    <div class="topbar">
        <div>
            <h1>Payment Review</h1>
            <p>Approve or reject manual UPI transaction records.</p>
        </div>
        <a class="btn back" href="dashboard.php">Dashboard</a>
    </div>

    <div class="filters">
        <form method="GET">
            <select name="status">
                <option value="">All Payments</option>
                <?php foreach($allowed_statuses as $option){ ?>
                    <option value="<?php echo $option; ?>" <?php if($status === $option) echo "selected"; ?>>
                        <?php echo $option; ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <table>
        <tr>
            <th>Product</th>
            <th>Buyer</th>
            <th>Seller</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php if($result && mysqli_num_rows($result) > 0){ ?>
            <?php while($payment = mysqli_fetch_assoc($result)){ ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($payment['title']); ?></strong><br>
                        #<?php echo (int)$payment['product_id']; ?><br>
                        <a class="btn view" target="_blank" href="../frontend/product-details.php?id=<?php echo (int)$payment['product_id']; ?>">View Product</a>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($payment['buyer_name']); ?><br>
                        <?php echo htmlspecialchars($payment['buyer_email']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($payment['seller_name']); ?><br>
                        <?php echo htmlspecialchars($payment['seller_email']); ?>
                    </td>
                    <td>
                        Rs. <?php echo htmlspecialchars($payment['amount']); ?><br>
                        <?php echo htmlspecialchars($payment['payment_method']); ?><br>
                        <b>TXN:</b> <?php echo htmlspecialchars($payment['transaction_id']); ?><br>
                        <?php echo date("d M Y", strtotime($payment['created_at'])); ?>
                    </td>
                    <td>
                        <span class="badge <?php echo htmlspecialchars($payment['status']); ?>">
                            <?php echo htmlspecialchars($payment['status']); ?>
                        </span>
                    </td>
                    <td>
                        <form class="action-form" method="POST" action="update-payment-status.php">
                            <input type="hidden" name="id" value="<?php echo (int)$payment['id']; ?>">
                            <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status); ?>">
                            <button class="btn approve" name="status" value="Approved" type="submit">Approve</button>
                            <button class="btn reject" name="status" value="Rejected" type="submit">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        <?php }else{ ?>
            <tr><td colspan="6">No payments found.</td></tr>
        <?php } ?>
    </table>
</main>
</body>
</html>
