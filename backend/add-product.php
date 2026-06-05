<?php

session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    die("Please Login First");
}

if(isset($_POST['add_product'])){

    $title = mysqli_real_escape_string($conn,$_POST['title']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $price = $_POST['price'];
    $category = mysqli_real_escape_string($conn,$_POST['category']);
    $status = mysqli_real_escape_string($conn,$_POST['status']);

    $seller_id = $_SESSION['user_id'];

    // Main File
    $file_name = time().'_'.$_FILES['file']['name'];
    $tmp_name = $_FILES['file']['tmp_name'];

    $file_extension = strtolower(
        pathinfo($file_name, PATHINFO_EXTENSION)
    );

    $allowed = [
        "jpg",
        "jpeg",
        "png",
        "pdf",
        "docx",
        "pptx",
        "mp4"
    ];

    if(!in_array($file_extension,$allowed)){
        die("Invalid File Type");
    }

    move_uploaded_file(
        $tmp_name,
        "../frontend/uploads/".$file_name
    );

    // Preview Image
    $preview_image = "";

    if(isset($_FILES['preview_image']) &&
       $_FILES['preview_image']['error']==0){

        $preview_image =
            time().'_preview_'.
            $_FILES['preview_image']['name'];

        move_uploaded_file(
            $_FILES['preview_image']['tmp_name'],
            "../frontend/uploads/".$preview_image
        );
    }

    $sql = "INSERT INTO products
    (
        title,
        description,
        price,
        image,
        seller_id,
        category,
        status,
        file_type,
        preview_image
    )
    VALUES
    (
        '$title',
        '$description',
        '$price',
        '$file_name',
        '$seller_id',
        '$category',
        '$status',
        '$file_extension',
        '$preview_image'
    )";

    if(mysqli_query($conn,$sql)){

        header("Location: ../frontend/my-products.php");
        exit();

    }else{

        echo mysqli_error($conn);

    }
}
?>