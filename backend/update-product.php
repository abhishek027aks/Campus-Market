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
    $uploadDir = "../frontend/uploads/";

    $allowedMain = ["jpg", "jpeg", "png", "pdf", "docx", "pptx", "mp4"];
    $allowedPreviewImage = ["jpg", "jpeg", "png"];
    $allowedPreviewFile = ["jpg", "jpeg", "png", "pdf", "mp4"];

    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){
        $newFile = time().'_'.basename($_FILES['file']['name']);
        $newExt = strtolower(pathinfo($newFile, PATHINFO_EXTENSION));

        if(!in_array($newExt, $allowedMain)){
            die("Invalid Main File Type");
        }

        if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir.$newFile)){
            $fileName = $newFile;
            $fileType = $newExt;
        }
    }

    if(isset($_FILES['preview_image']) && $_FILES['preview_image']['error'] == 0){
        $newPreviewImage = time().'_preview_image_'.basename($_FILES['preview_image']['name']);
        $newPreviewImageExt = strtolower(pathinfo($newPreviewImage, PATHINFO_EXTENSION));

        if(!in_array($newPreviewImageExt, $allowedPreviewImage)){
            die("Invalid Thumbnail Type");
        }

        if(move_uploaded_file($_FILES['preview_image']['tmp_name'], $uploadDir.$newPreviewImage)){
            $previewImage = $newPreviewImage;
        }
    }

    if(isset($_FILES['preview_file']) && $_FILES['preview_file']['error'] == 0){
        $newPreviewFile = time().'_preview_file_'.basename($_FILES['preview_file']['name']);
        $newPreviewFileExt = strtolower(pathinfo($newPreviewFile, PATHINFO_EXTENSION));

        if(!in_array($newPreviewFileExt, $allowedPreviewFile)){
            die("Invalid Preview File Type");
        }

        if(move_uploaded_file($_FILES['preview_file']['tmp_name'], $uploadDir.$newPreviewFile)){
            $previewFile = $newPreviewFile;
        }
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
