<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "campus_market"
);

if(!$conn){
    die("Connection Failed");
}

if(!function_exists("add_user_column_if_missing")){
    function add_user_column_if_missing($conn, $column, $definition){
        $column = mysqli_real_escape_string($conn, $column);
        $check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$column'");

        if($check && mysqli_num_rows($check) == 0){
            mysqli_query($conn, "ALTER TABLE users ADD $definition");
        }
    }
}

if(!function_exists("add_product_column_if_missing")){
    function add_product_column_if_missing($conn, $column, $definition){
        $column = mysqli_real_escape_string($conn, $column);
        $check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE '$column'");

        if($check && mysqli_num_rows($check) == 0){
            mysqli_query($conn, "ALTER TABLE products ADD $definition");
        }
    }
}

add_user_column_if_missing(
    $conn,
    "profile_photo",
    "profile_photo VARCHAR(255) DEFAULT ''"
);

add_user_column_if_missing(
    $conn,
    "roll_number",
    "roll_number VARCHAR(50) DEFAULT ''"
);

add_user_column_if_missing(
    $conn,
    "semester",
    "semester VARCHAR(50) DEFAULT ''"
);

add_user_column_if_missing(
    $conn,
    "course",
    "course VARCHAR(100) DEFAULT ''"
);

add_user_column_if_missing(
    $conn,
    "college_name",
    "college_name VARCHAR(150) DEFAULT ''"
);

add_user_column_if_missing(
    $conn,
    "college_id_card",
    "college_id_card VARCHAR(255) DEFAULT ''"
);

add_user_column_if_missing(
    $conn,
    "verification_status",
    "verification_status VARCHAR(20) DEFAULT 'Not Submitted'"
);

add_product_column_if_missing(
    $conn,
    "views",
    "views INT DEFAULT 0"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

$admin_check = mysqli_query($conn, "SELECT id FROM admins LIMIT 1");

if($admin_check && mysqli_num_rows($admin_check) == 0){
    mysqli_query(
        $conn,
        "INSERT INTO admins(username,password)
         VALUES('admin','admin123')"
    );
}

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, product_id)
    )"
);
?>
