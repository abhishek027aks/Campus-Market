<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$users_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users")
);

$pending_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE verification_status='Pending'")
);

$approved_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE verification_status='Approved'")
);

$products_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM products")
);

$pending_products_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE approval_status='Pending'")
);

$notices_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM notices")
);

$pending_payments_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM payments WHERE status='Pending'")
);

$pending_reports_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM product_reports WHERE status='Pending'")
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Campus Market</title>
<link rel="stylesheet" href="../frontend/css/style.css">

<style>
body{
    background:#f4f7fc;
    font-family:Arial,sans-serif;
    color:#1f2937;
}

.admin-wrap{
    width:1180px;
    max-width:95%;
    margin:35px auto;
}

.admin-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
    gap:15px;
}

.admin-header p{
    color:#6b7280;
    margin-top:6px;
}

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-top:22px;
}

.stat-card,
.section-card{
    background:white;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
}

.stat-card{
    padding:20px;
}

.stat-card h2{
    color:#0d6efd;
    font-size:34px;
    margin:0;
}

.stat-card p{
    color:#6b7280;
    margin:8px 0 0;
}

.section-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-top:22px;
}

.section-card{
    padding:20px;
}

.section-card h2{
    margin:0 0 8px;
    font-size:22px;
}

.section-card p{
    color:#6b7280;
    margin:0 0 15px;
}

.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.admin-btn{
    display:inline-block;
    padding:12px 18px;
    color:white;
    text-decoration:none;
    border-radius:6px;
    background:#0d6efd;
}

.secondary{
    background:#6f42c1;
}

.success{
    background:#198754;
}

.warning{
    background:#fd7e14;
    color:#111;
}

.danger{
    background:#dc3545;
}

.logout{
    background:#dc3545;
}

@media(max-width:768px){
    .admin-header{
        flex-direction:column;
        gap:12px;
        align-items:flex-start;
    }

    .stats,
    .section-grid{
        grid-template-columns:1fr;
    }
}
</style>
</head>
<body>

<div class="admin-wrap">
    <div class="admin-header">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>. Manage users, products, reports, payments and notices from one place.</p>
        </div>

        <a class="admin-btn logout" href="logout.php">Logout</a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h2><?php echo (int)$users_count['total']; ?></h2>
            <p>Total Users</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$pending_count['total']; ?></h2>
            <p>Pending Students</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$approved_count['total']; ?></h2>
            <p>Approved Students</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$products_count['total']; ?></h2>
            <p>Total Products</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h2><?php echo (int)$pending_products_count['total']; ?></h2>
            <p>Pending Products</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$notices_count['total']; ?></h2>
            <p>Notices</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$pending_payments_count['total']; ?></h2>
            <p>Pending Payments</p>
        </div>

        <div class="stat-card">
            <h2><?php echo (int)$pending_reports_count['total']; ?></h2>
            <p>Product Reports</p>
        </div>
    </div>

    <div class="section-grid">
        <section class="section-card">
            <h2>Users</h2>
            <p>Review student profiles and verification requests.</p>
            <div class="actions">
                <a class="admin-btn" href="users.php">Student Verification</a>
            </div>
        </section>

        <section class="section-card">
            <h2>Products</h2>
            <p>Approve marketplace listings and handle reported products.</p>
            <div class="actions">
                <a class="admin-btn success" href="products.php">Manage Products</a>
                <a class="admin-btn danger" href="reports.php">Review Reports</a>
            </div>
        </section>

        <section class="section-card">
            <h2>Operations</h2>
            <p>Manage payment reviews and campus notices.</p>
            <div class="actions">
                <a class="admin-btn warning" href="payments.php">Review Payments</a>
                <a class="admin-btn secondary" href="notices.php">Manage Notices</a>
            </div>
        </section>
    </div>
</div>

</body>
</html>
