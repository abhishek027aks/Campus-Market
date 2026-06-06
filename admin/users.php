<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$status = "";

if(isset($_GET['status']) && $_GET['status'] != ""){
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    $sql = "SELECT * FROM users
            WHERE verification_status='$status'
            ORDER BY id DESC";
}else{
    $sql = "SELECT * FROM users ORDER BY id DESC";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users - Admin</title>
<link rel="stylesheet" href="../frontend/css/style.css">

<style>
body{
    background:#f4f7fc;
}

.admin-wrap{
    width:1150px;
    max-width:96%;
    margin:30px auto;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:white;
    padding:18px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
    margin-bottom:18px;
}

.filter-box{
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
    margin-bottom:18px;
}

.filter-box select,
.filter-box button{
    padding:10px;
    margin-right:8px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
}

th,
td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:left;
    vertical-align:top;
}

th{
    background:#0d6efd;
    color:white;
}

.status{
    display:inline-block;
    padding:6px 10px;
    border-radius:20px;
    color:white;
    font-size:13px;
}

.NotSubmitted{
    background:#6c757d;
}

.Pending{
    background:#ffc107;
    color:#111;
}

.Approved{
    background:#198754;
}

.Rejected{
    background:#dc3545;
}

.btn-small{
    display:inline-block;
    padding:8px 10px;
    color:white;
    text-decoration:none;
    border-radius:5px;
    margin:3px 0;
    font-size:13px;
}

.approve{
    background:#198754;
}

.reject{
    background:#dc3545;
}

.view{
    background:#6f42c1;
}

.back{
    background:#0d6efd;
}

@media(max-width:900px){
    table{
        display:block;
        overflow-x:auto;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }
}
</style>
</head>
<body>

<div class="admin-wrap">
    <div class="topbar">
        <div>
            <h1>Student Verification</h1>
            <p>Approve or reject uploaded college ID cards.</p>
        </div>

        <a class="btn-small back" href="dashboard.php">Dashboard</a>
    </div>

    <div class="filter-box">
        <form method="GET">
            <select name="status">
                <option value="">All Students</option>
                <option value="Not Submitted" <?php if($status=="Not Submitted") echo "selected"; ?>>Not Submitted</option>
                <option value="Pending" <?php if($status=="Pending") echo "selected"; ?>>Pending</option>
                <option value="Approved" <?php if($status=="Approved") echo "selected"; ?>>Approved</option>
                <option value="Rejected" <?php if($status=="Rejected") echo "selected"; ?>>Rejected</option>
            </select>

            <button type="submit">Filter</button>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Academic Details</th>
            <th>ID Card</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while($user = mysqli_fetch_assoc($result)){ ?>
            <?php
            $statusClass = str_replace(" ", "", $user['verification_status']);
            ?>
            <tr>
                <td><?php echo (int)$user['id']; ?></td>

                <td>
                    <strong><?php echo htmlspecialchars($user['fullname']); ?></strong><br>
                    <?php echo htmlspecialchars($user['email']); ?>
                </td>

                <td>
                    <strong>Roll:</strong> <?php echo htmlspecialchars($user['roll_number']); ?><br>
                    <strong>Semester:</strong> <?php echo htmlspecialchars($user['semester']); ?><br>
                    <strong>Course:</strong> <?php echo htmlspecialchars($user['course']); ?><br>
                    <strong>College:</strong> <?php echo htmlspecialchars($user['college_name']); ?>
                </td>

                <td>
                    <?php if(!empty($user['college_id_card'])){ ?>
                        <a
                        class="btn-small view"
                        target="_blank"
                        href="../frontend/uploads/<?php echo htmlspecialchars($user['college_id_card']); ?>">
                            View ID
                        </a>
                    <?php }else{ ?>
                        Not uploaded
                    <?php } ?>
                </td>

                <td>
                    <span class="status <?php echo htmlspecialchars($statusClass); ?>">
                        <?php echo htmlspecialchars($user['verification_status']); ?>
                    </span>
                </td>

                <td>
                    <?php if(!empty($user['college_id_card'])){ ?>
                        <a
                        class="btn-small approve"
                        href="update-verification.php?id=<?php echo (int)$user['id']; ?>&status=Approved">
                            Approve
                        </a>

                        <a
                        class="btn-small reject"
                        href="update-verification.php?id=<?php echo (int)$user['id']; ?>&status=Rejected">
                            Reject
                        </a>
                    <?php }else{ ?>
                        Waiting for ID
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
