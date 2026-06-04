<?php

session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    die("Please Login First");
}

if(isset($_POST['add_product'])){

    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $seller_id = $_SESSION['user_id'];

    $image_name = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];

    move_uploaded_file(
        $tmp_name,
        "../frontend/uploads/".$image_name
    );

    $sql = "INSERT INTO products
            (title,description,price,image,seller_id)
            VALUES
            ('$title','$description','$price','$image_name','$seller_id')";

    if(mysqli_query($conn,$sql)){
        echo "Product Added Successfully";
    }else{
        echo mysqli_error($conn);
    }
}
?>