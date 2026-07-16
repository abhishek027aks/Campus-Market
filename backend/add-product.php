<?php

session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    die("Please Login First");
}

if(isset($_POST['add_product'])){

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);

    if(
        $category == "Others" &&
        isset($_POST['other_category']) &&
        !empty(trim($_POST['other_category']))
    ){
        $category = trim($_POST['other_category']);
    }

    $status = trim($_POST['status']);

    $seller_id = (int)$_SESSION['user_id'];

    /* =========================
       MAIN FILE
    ========================= */

    if(!isset($_FILES['file']) ||
       $_FILES['file']['error'] != 0){

        die("Please Select File");
    }

    $allowed = [
        "jpg",
        "jpeg",
        "png",
        "pdf",
        "docx",
        "pptx",
        "mp4"
    ];

    $file_name = campus_save_uploaded_file(
        $_FILES['file'],
        __DIR__ . "/../frontend/uploads",
        "product",
        $allowed,
        true
    );

    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    /* =========================
       PREVIEW IMAGE
    ========================= */

    $preview_image = "";

    if(
        isset($_FILES['preview_image']) &&
        $_FILES['preview_image']['error'] == 0
    ){

        $preview_image = campus_save_uploaded_file(
            $_FILES['preview_image'],
            __DIR__ . "/../frontend/uploads",
            "preview_image",
            ["jpg", "jpeg", "png"],
            false
        );
    }

    /* =========================
       PREVIEW FILE
    ========================= */

    $preview_file = "";

    if(
        isset($_FILES['preview_file']) &&
        $_FILES['preview_file']['error'] == 0
    ){

        $preview_file = campus_save_uploaded_file(
            $_FILES['preview_file'],
            __DIR__ . "/../frontend/uploads",
            "preview_file",
            ["jpg", "jpeg", "png", "pdf", "mp4"],
            false
        );
    }

    /* =========================
       INSERT PRODUCT
    ========================= */

    $approval_status = "Pending";
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO products
         (
            title,
            description,
            price,
            image,
            seller_id,
            category,
            status,
            file_type,
            preview_image,
            preview_file,
            approval_status
         )
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt,
        "ssssissssss",
        $title,
        $description,
        $price,
        $file_name,
        $seller_id,
        $category,
        $status,
        $file_extension,
        $preview_image,
        $preview_file,
        $approval_status
    );

    if(mysqli_stmt_execute($stmt)){

        header(
            "Location: ../frontend/my-products.php"
        );
        exit();

    }else{

        echo mysqli_stmt_error($stmt);

    }
}
?>
