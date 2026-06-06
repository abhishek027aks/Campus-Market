<?php
session_start();
include "../backend/config.php";

if(isset($_SESSION['admin_id'])){
    header("Location: dashboard.php");
    exit();
}

$error = "";

if(isset($_POST['admin_login'])){
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $sql = "SELECT * FROM admins
            WHERE username='$username'
            AND password='$password'";

    $result = mysqli_query($conn, $sql);

    if($result && mysqli_num_rows($result) > 0){
        $admin = mysqli_fetch_assoc($result);

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: dashboard.php");
        exit();
    }else{
        $error = "Invalid admin username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Campus Market</title>
<link rel="stylesheet" href="../frontend/css/style.css">

<style>
body{
    background:#eef3f8;
}

.admin-login{
    max-width:420px;
    margin:80px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 0 12px rgba(0,0,0,0.1);
}

.admin-login h1{
    text-align:center;
    margin-bottom:20px;
}

.error{
    background:#f8d7da;
    color:#842029;
    padding:12px;
    border-radius:6px;
    margin-bottom:15px;
}

.hint{
    color:#666;
    font-size:14px;
    margin-top:12px;
    text-align:center;
}
</style>
</head>
<body>

<div class="admin-login">
    <h1>Admin Login</h1>

    <?php if(!empty($error)){ ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST">
        <input
        type="text"
        name="username"
        placeholder="Username"
        required>

        <input
        type="password"
        name="password"
        placeholder="Password"
        required>

        <button
        type="submit"
        name="admin_login">
            Login
        </button>
    </form>

    <p class="hint">Default: admin / admin123</p>
</div>

</body>
</html>
