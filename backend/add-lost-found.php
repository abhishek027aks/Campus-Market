<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../frontend/login.html");
    exit();
}

if(!isset($_POST['post_lost_found'])){
    die("Invalid Request");
}

$user_id = (int)$_SESSION['user_id'];
$item_type = mysqli_real_escape_string($conn, $_POST['item_type']);
$title = mysqli_real_escape_string($conn, trim($_POST['title']));
$description = mysqli_real_escape_string($conn, trim($_POST['description']));
$location = mysqli_real_escape_string($conn, trim($_POST['location']));
$contact = mysqli_real_escape_string($conn, trim($_POST['contact']));

$allowed_types = ["Lost", "Found"];

if(!in_array($item_type, $allowed_types, true)){
    die("Invalid Item Type");
}

if($title === "" || $description === "" || $location === "" || $contact === ""){
    die("All fields are required");
}

$image = "";

if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
    $image = campus_save_uploaded_file(
        $_FILES['image'],
        __DIR__ . "/../frontend/uploads",
        "lost_found",
        ["jpg", "jpeg", "png"],
        false
    );
}

$sql = "INSERT INTO lost_found
        (user_id, item_type, title, description, location, contact, image)
        VALUES
        ('$user_id', '$item_type', '$title', '$description', '$location', '$contact', '$image')";

if(mysqli_query($conn, $sql)){
    header("Location: ../frontend/lost-found.php");
    exit();
}

echo "Post Failed: " . mysqli_error($conn);
?>
