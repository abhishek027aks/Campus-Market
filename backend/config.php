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

add_product_column_if_missing(
    $conn,
    "downloads",
    "downloads INT DEFAULT 0"
);

add_product_column_if_missing(
    $conn,
    "is_featured",
    "is_featured TINYINT(1) NOT NULL DEFAULT 0"
);

add_product_column_if_missing(
    $conn,
    "category",
    "category VARCHAR(100) DEFAULT 'Others'"
);

add_product_column_if_missing(
    $conn,
    "status",
    "status VARCHAR(30) DEFAULT 'Available'"
);

add_product_column_if_missing(
    $conn,
    "file_type",
    "file_type VARCHAR(20) DEFAULT ''"
);

add_product_column_if_missing(
    $conn,
    "preview_image",
    "preview_image VARCHAR(255) DEFAULT ''"
);

add_product_column_if_missing(
    $conn,
    "preview_file",
    "preview_file VARCHAR(255) DEFAULT ''"
);

$approval_column = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM products LIKE 'approval_status'"
);

if($approval_column && mysqli_num_rows($approval_column) == 0){
    mysqli_query(
        $conn,
        "ALTER TABLE products
         ADD approval_status VARCHAR(20) NOT NULL DEFAULT 'Pending'"
    );

    // Products created before moderation existed remain publicly available.
    mysqli_query($conn, "UPDATE products SET approval_status='Approved'");
}

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

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        rating TINYINT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_product_review (user_id, product_id)
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS chats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        buyer_id INT NOT NULL,
        seller_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_chat (product_id, buyer_id, seller_id)
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_id INT NOT NULL,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        link VARCHAR(255) DEFAULT '',
        is_read TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS lost_found (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        item_type VARCHAR(20) NOT NULL,
        title VARCHAR(150) NOT NULL,
        description TEXT NOT NULL,
        location VARCHAR(150) NOT NULL,
        contact VARCHAR(120) NOT NULL,
        image VARCHAR(255) DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'Open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS notices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        title VARCHAR(180) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(50) NOT NULL,
        notice_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        buyer_id INT NOT NULL,
        seller_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(30) NOT NULL,
        transaction_id VARCHAR(120) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS product_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        reporter_id INT NOT NULL,
        seller_id INT NOT NULL,
        reason VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME DEFAULT NULL
    )"
);
?>
