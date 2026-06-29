<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$allowed_statuses = ["Pending", "Reviewed"];
$status = isset($_GET['status']) ? $_GET['status'] : "";

if($status !== "" && !in_array($status, $allowed_statuses, true)){
    $status = "";
}

$sql = "SELECT product_reports.*,
        products.title,
        reporter.fullname AS reporter_name,
        reporter.email AS reporter_email,
        seller.fullname AS seller_name,
        seller.email AS seller_email
        FROM product_reports
        JOIN products ON product_reports.product_id = products.id
        JOIN users AS reporter ON product_reports.reporter_id = reporter.id
        JOIN users AS seller ON product_reports.seller_id = seller.id";

if($status !== ""){
    $safe_status = mysqli_real_escape_string($conn, $status);
    $sql .= " WHERE product_reports.status='$safe_status'";
}

$sql .= " ORDER BY product_reports.id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Product Reports - Admin</title>
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
.Pending{background:#ffc107;color:#111}.Reviewed{background:#198754}
.btn{display:inline-block;padding:8px 10px;border:0;border-radius:5px;color:#fff;text-decoration:none;cursor:pointer;margin:2px}
.reviewed{background:#198754}.view{background:#6f42c1}.back{background:#0d6efd}.reject{background:#fd7e14;color:#111}.delete{background:#dc3545}
.action-form{display:inline}
@media(max-width:900px){table{display:block;overflow-x:auto}.topbar{align-items:flex-start;flex-direction:column}}
</style>
</head>
<body>
<main class="admin-wrap">
    <div class="topbar">
        <div>
            <h1>Product Reports</h1>
            <p>Review reports submitted by marketplace users.</p>
        </div>
        <a class="btn back" href="dashboard.php">Dashboard</a>
    </div>

    <div class="filters">
        <form method="GET">
            <select name="status">
                <option value="">All Reports</option>
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
            <th>Reporter</th>
            <th>Seller</th>
            <th>Report</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php if($result && mysqli_num_rows($result) > 0){ ?>
            <?php while($report = mysqli_fetch_assoc($result)){ ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($report['title']); ?></strong><br>
                        #<?php echo (int)$report['product_id']; ?><br>
                        <a class="btn view" target="_blank" href="../frontend/product-details.php?id=<?php echo (int)$report['product_id']; ?>">View Product</a>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($report['reporter_name']); ?><br>
                        <?php echo htmlspecialchars($report['reporter_email']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($report['seller_name']); ?><br>
                        <?php echo htmlspecialchars($report['seller_email']); ?>
                    </td>
                    <td>
                        <b><?php echo htmlspecialchars($report['reason']); ?></b><br>
                        <?php echo nl2br(htmlspecialchars($report['description'])); ?><br>
                        <small><?php echo date("d M Y", strtotime($report['created_at'])); ?></small>
                    </td>
                    <td>
                        <span class="badge <?php echo htmlspecialchars($report['status']); ?>">
                            <?php echo htmlspecialchars($report['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($report['status'] !== "Reviewed"){ ?>
                            <form class="action-form" method="POST" action="update-report-status.php">
                                <input type="hidden" name="id" value="<?php echo (int)$report['id']; ?>">
                                <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status); ?>">
                                <button class="btn reviewed" name="status" value="Reviewed" type="submit">Mark Reviewed</button>
                            </form>
                        <?php }else{ ?>
                            Reviewed
                        <?php } ?>

                        <form class="action-form" method="POST" action="report-product-action.php">
                            <input type="hidden" name="report_id" value="<?php echo (int)$report['id']; ?>">
                            <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status); ?>">
                            <button class="btn reject" name="action" value="reject_product" type="submit">Reject Product</button>
                            <button class="btn delete" name="action" value="delete_product" type="submit">Delete Product</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        <?php }else{ ?>
            <tr><td colspan="6">No reports found.</td></tr>
        <?php } ?>
    </table>
</main>
</body>
</html>
