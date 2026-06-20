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

$sql = "SELECT products.*, users.fullname, users.email
        FROM products
        JOIN users ON products.seller_id = users.id";

if($status !== ""){
    $safe_status = mysqli_real_escape_string($conn, $status);
    $sql .= " WHERE products.approval_status='$safe_status'";
}

$sql .= " ORDER BY products.id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Products - Admin</title>
<link rel="stylesheet" href="../frontend/css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.admin-wrap{width:1180px;max-width:96%;margin:30px auto}
.topbar,.filters{background:#fff;padding:18px;margin-bottom:18px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08)}
.topbar{display:flex;justify-content:space-between;align-items:center}
.filters select,.filters button{padding:10px;margin-right:8px}
table{width:100%;border-collapse:collapse;background:#fff;box-shadow:0 0 10px rgba(0,0,0,.08)}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
th{background:#0d6efd;color:#fff}
.badge{display:inline-block;padding:6px 10px;border-radius:16px;color:#fff;font-size:13px}
.Pending{background:#ffc107;color:#111}.Approved{background:#198754}.Rejected{background:#dc3545}
.btn{display:inline-block;padding:8px 10px;border:0;border-radius:5px;color:#fff;text-decoration:none;cursor:pointer;margin:2px}
.approve{background:#198754}.reject{background:#dc3545}.view{background:#6f42c1}.back{background:#0d6efd}
.action-form{display:inline}
@media(max-width:900px){table{display:block;overflow-x:auto}.topbar{align-items:flex-start;flex-direction:column;gap:10px}}
</style>
</head>
<body>
<main class="admin-wrap">
    <div class="topbar">
        <div><h1>Product Approval</h1><p>Review products before marketplace publication.</p></div>
        <a class="btn back" href="dashboard.php">Dashboard</a>
    </div>

    <div class="filters">
        <form method="GET">
            <select name="status">
                <option value="">All Products</option>
                <?php foreach($allowed_statuses as $option){ ?>
                    <option value="<?php echo $option; ?>" <?php if($status === $option) echo "selected"; ?>><?php echo $option; ?></option>
                <?php } ?>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <table>
        <tr><th>Product</th><th>Seller</th><th>Details</th><th>Approval</th><th>Action</th></tr>
        <?php while($product = mysqli_fetch_assoc($result)){ ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($product['title']); ?></strong><br>#<?php echo (int)$product['id']; ?></td>
            <td><?php echo htmlspecialchars($product['fullname']); ?><br><?php echo htmlspecialchars($product['email']); ?></td>
            <td>Rs. <?php echo htmlspecialchars($product['price']); ?><br><?php echo htmlspecialchars($product['category']); ?><br><?php echo htmlspecialchars($product['status']); ?></td>
            <td><span class="badge <?php echo htmlspecialchars($product['approval_status']); ?>"><?php echo htmlspecialchars($product['approval_status']); ?></span></td>
            <td>
                <a class="btn view" target="_blank" href="../frontend/product-details.php?id=<?php echo (int)$product['id']; ?>">Preview</a>
                <form class="action-form" method="POST" action="update-product-approval.php">
                    <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                    <button class="btn approve" name="status" value="Approved" type="submit">Approve</button>
                    <button class="btn reject" name="status" value="Rejected" type="submit">Reject</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</main>
</body>
</html>
