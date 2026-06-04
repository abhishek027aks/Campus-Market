<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h1>Welcome <?php echo $_SESSION['fullname']; ?></h1>

<p>Login Successful</p>

<a href="../backend/logout.php">Logout</a>

</body>
</html>