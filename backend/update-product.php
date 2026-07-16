<?php

session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    die("Please Login First");
}

if(isset($_POST['update_product'])){

    $id = (int)$_POST['id'];
    $user_id = (int)$_SESSION['user_id'];

    $title = trim($_POST['title']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $status = trim($_POST['status']);

    if(
        $category == "Others" &&
        isset($_POST['other_category']) &&
        !empty(trim($_POST['other_category']))
    ){
        $category = trim($_POST['other_category']);
    }

    $productStmt = mysqli_prepare(
        $conn,
        "SELECT * FROM products
         WHERE id=?
         AND seller_id=?"
    );
    mysqli_stmt_bind_param($productStmt, "ii", $id, $user_id);
    mysqli_stmt_execute($productStmt);
    $productResult = mysqli_stmt_get_result($productStmt);
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

    $approvalStatus = "Pending";
    $updateStmt = mysqli_prepare(
        $conn,
        "UPDATE products
         SET
         title=?,
         price=?,
         description=?,
         category=?,
         status=?,
         image=?,
         file_type=?,
         preview_image=?,
         preview_file=?,
         approval_status=?
         WHERE id=?
         AND seller_id=?"
    );
    mysqli_stmt_bind_param(
        $updateStmt,
        "ssssssssssii",
        $title,
        $price,
        $description,
        $category,
        $status,
        $fileName,
        $fileType,
        $previewImage,
        $previewFile,
        $approvalStatus,
        $id,
        $user_id
    );

    if(mysqli_stmt_execute($updateStmt)){
        header("Location: ../frontend/my-products.php");
        exit();
    }else{
        echo "Database Error: " . mysqli_stmt_error($updateStmt);
    }

}else{
    echo "Invalid Request";
}

?>
