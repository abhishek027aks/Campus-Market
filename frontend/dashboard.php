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
    <title>Dashboard - Campus Market</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="form-container">

    <h1>Welcome <?php echo $_SESSION['fullname']; ?></h1>

    <p>Login Successful ✅</p>

    <br>

    <a href="products.php">
        <button>View Products</button>
    </a>

    <br><br>

    <a href="sell.html">
        <button>Sell Product</button>
    </a>

    <br><br>

    <a href="my-products.php">
        <button>My Products</button>
    </a>

    <br><br>

    <a href="../backend/logout.php">
        <button>Logout</button>
    </a>

</div>

</body>
</html>