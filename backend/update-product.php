<?php

session_start();
include "config.php";

if(isset($_POST['update_product'])){

    $id = $_POST['id'];
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $sql = "UPDATE products
            SET
            title='$title',
            price='$price',
            description='$description'
            WHERE id='$id'";

    if(mysqli_query($conn,$sql)){

        header("Location: ../frontend/my-products.php");
        exit();

    }else{

        echo mysqli_error($conn);

    }
}

?>