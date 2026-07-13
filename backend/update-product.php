<?php

session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    die("Please Login First");
}

if(isset($_POST['update_product'])){

    $id = (int)$_POST['id'];
    $user_id = (int)$_SESSION['user_id'];

    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $price = mysqli_real_escape_string($conn, trim($_POST['price']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    $status = mysqli_real_escape_string($conn, trim($_POST['status']));

    if(
        $category == "Others" &&
        isset($_POST['other_category']) &&
        !empty(trim($_POST['other_category']))
    ){
        $category = mysqli_real_escape_string($conn, trim($_POST['other_category']));
    }

    $productSql = "SELECT * FROM products
                   WHERE id='$id'
                   AND seller_id='$user_id'";

    $productResult = mysqli_query($conn, $productSql);
    $product = mysqli_fetch_assoc($productResult);

    if(!$product){
        die("Product Not Found");
    }

    $fileName = $product['image'];
    $fileType = $product['file_type'];
    $previewImage = $product['preview_image'];
    $previewFile = $product['preview_file'];
    $uploadDir = __DIR__ . "/../frontend/uploads";

    $allowedMain = ["jpg", "jpeg", "png", "pdf", "docx", "pptx", "mp4"];
    $allowedPreviewImage = ["jpg", "jpeg", "png"];
    $allowedPreviewFile = ["jpg", "jpeg", "png", "pdf", "mp4"];

    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){
        $newFile = campus_save_uploaded_file($_FILES['file'], $uploadDir, "product", $allowedMain, false);
        $fileName = $newFile;
        $fileType = strtolower(pathinfo($newFile, PATHINFO_EXTENSION));
    }

    if(isset($_FILES['preview_image']) && $_FILES['preview_image']['error'] == 0){
        $previewImage = campus_save_uploaded_file($_FILES['preview_image'], $uploadDir, "preview_image", $allowedPreviewImage, false);
    }

    if(isset($_FILES['preview_file']) && $_FILES['preview_file']['error'] == 0){
        $previewFile = campus_save_uploaded_file($_FILES['preview_file'], $uploadDir, "preview_file", $allowedPreviewFile, false);
    }

    $sql = "UPDATE products
            SET
            title='$title',
            price='$price',
            description='$description',
            category='$category',
            status='$status',
            image='$fileName',
            file_type='$fileType',
            preview_image='$previewImage',
            preview_file='$previewFile',
            approval_status='Pending'
            WHERE id='$id'
            AND seller_id='$user_id'";

    if(mysqli_query($conn, $sql)){
        header("Location: ../frontend/my-products.php");
        exit();
    }else{
        echo "Database Error: " . mysqli_error($conn);
    }

}else{
    echo "Invalid Request";
}

?>
