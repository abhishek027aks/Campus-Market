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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Lost & Found - Campus Market</title>
<link rel="stylesheet" href="css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.form-box{width:560px;max-width:94%;margin:35px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08)}
label{display:block;font-weight:bold;margin-top:12px}
input,select,textarea{width:100%;padding:11px;margin-top:7px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box}
textarea{min-height:120px;resize:vertical}
.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.btn{display:inline-block;padding:11px 16px;border-radius:6px;background:#0d6efd;color:#fff;text-decoration:none;border:0;cursor:pointer}
.back{background:#6c757d}
</style>
</head>
<body>
<div class="form-box">
    <h1>Post Lost & Found Item</h1>

    <form action="../backend/add-lost-found.php" method="POST" enctype="multipart/form-data">
        <label>Item Type</label>
        <select name="item_type" required>
            <option value="">Select Type</option>
            <option value="Lost">Lost</option>
            <option value="Found">Found</option>
        </select>

        <label>Title</label>
        <input type="text" name="title" placeholder="Lost wallet, Found ID card..." required>

        <label>Description</label>
        <textarea name="description" placeholder="Add details that help identify the item" required></textarea>

        <label>Location</label>
        <input type="text" name="location" placeholder="Library, canteen, classroom..." required>

        <label>Contact</label>
        <input type="text" name="contact" placeholder="Phone or email" required>

        <label>Image Optional</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,image/jpeg,image/png">

        <div class="actions">
            <button class="btn" type="submit" name="post_lost_found">Post Item</button>
            <a class="btn back" href="lost-found.php">Back</a>
        </div>
    </form>
</div>
</body>
</html>
