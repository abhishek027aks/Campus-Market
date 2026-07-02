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
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM admins
            WHERE username='$username'
            LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if($result && mysqli_num_rows($result) > 0){
        $admin = mysqli_fetch_assoc($result);

        if(campus_password_matches($password, $admin['password'])){
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            $password_info = password_get_info($admin['password']);

            if(!isset($password_info['algoName']) || $password_info['algoName'] === "unknown"){
                $new_hash = mysqli_real_escape_string(
                    $conn,
                    password_hash($password, PASSWORD_DEFAULT)
                );
                $admin_id = (int)$admin['id'];
                mysqli_query($conn, "UPDATE admins SET password='$new_hash' WHERE id='$admin_id'");
            }

            header("Location: dashboard.php");
            exit();
        }
    }

    $error = "Invalid admin username or password";
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
