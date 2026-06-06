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
}

.admin-wrap{
    width:1000px;
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
}

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-top:22px;
}

.stat-card{
    background:white;
    padding:22px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
}

.stat-card h2{
    color:#0d6efd;
    font-size:34px;
}

.actions{
    margin-top:22px;
    display:flex;
    gap:12px;
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

.logout{
    background:#dc3545;
}

@media(max-width:768px){
    .admin-header{
        flex-direction:column;
        gap:12px;
        align-items:flex-start;
    }

    .stats{
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
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
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

    <div class="actions">
        <a class="admin-btn" href="users.php">Manage Student Verification</a>
        <a class="admin-btn" href="../frontend/products.php">View Products</a>
    </div>
</div>

</body>
</html>
