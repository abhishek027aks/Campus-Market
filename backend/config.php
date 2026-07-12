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

if(!function_exists("campus_password_matches")){
    function campus_password_matches($plain_password, $stored_password){
        $password_info = password_get_info($stored_password);

        if(isset($password_info['algoName']) && $password_info['algoName'] !== "unknown"){
            return password_verify($plain_password, $stored_password);
        }

        return hash_equals((string)$stored_password, (string)$plain_password);
    }
}

if(!function_exists("campus_seed_permission")){
    function campus_seed_permission($conn, $name, $description){
        $safe_name = mysqli_real_escape_string($conn, $name);
        $safe_description = mysqli_real_escape_string($conn, $description);

        mysqli_query(
            $conn,
            "INSERT INTO permissions(name, description)
             VALUES('$safe_name', '$safe_description')
             ON DUPLICATE KEY UPDATE description=VALUES(description)"
        );
    }
}

if(!function_exists("campus_admin_has_permission")){
    function campus_admin_has_permission($conn, $admin_id, $permission_name){
        $admin_id = (int)$admin_id;
        $safe_permission = mysqli_real_escape_string($conn, $permission_name);

        $result = mysqli_query(
            $conn,
            "SELECT permissions.id
             FROM permissions
             JOIN role_permissions ON permissions.id = role_permissions.permission_id
             JOIN admin_roles ON role_permissions.role_id = admin_roles.role_id
             WHERE admin_roles.admin_id='$admin_id'
             AND permissions.name='$safe_permission'
             LIMIT 1"
        );

        return $result && mysqli_num_rows($result) > 0;
    }
}

if(!function_exists("campus_admin_permissions")){
    function campus_admin_permissions($conn, $admin_id){
        $admin_id = (int)$admin_id;
        $permissions = [];

        $result = mysqli_query(
            $conn,
            "SELECT DISTINCT permissions.name
             FROM permissions
             JOIN role_permissions ON permissions.id = role_permissions.permission_id
             JOIN admin_roles ON role_permissions.role_id = admin_roles.role_id
             WHERE admin_roles.admin_id='$admin_id'
             ORDER BY permissions.name"
        );

        if($result){
            while($row = mysqli_fetch_assoc($result)){
                $permissions[] = $row['name'];
            }
        }

        return $permissions;
    }
}

if(!function_exists("campus_require_admin_permission")){
    function campus_require_admin_permission($conn, $permission_name){
        if(!isset($_SESSION['admin_id'])){
            header("Location: login.php");
            exit();
        }

        if(!campus_admin_has_permission($conn, $_SESSION['admin_id'], $permission_name)){
            http_response_code(403);
            die("Access denied");
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

$email_verified_column = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM users LIKE 'email_verified'"
);

if($email_verified_column && mysqli_num_rows($email_verified_column) == 0){
    mysqli_query(
        $conn,
        "ALTER TABLE users
         ADD email_verified TINYINT(1) NOT NULL DEFAULT 1"
    );
}

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

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description VARCHAR(255) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description VARCHAR(255) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS role_permissions (
        role_id INT NOT NULL,
        permission_id INT NOT NULL,
        PRIMARY KEY(role_id, permission_id)
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS admin_roles (
        admin_id INT NOT NULL,
        role_id INT NOT NULL,
        PRIMARY KEY(admin_id, role_id)
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS user_roles (
        user_id INT NOT NULL,
        role_id INT NOT NULL,
        PRIMARY KEY(user_id, role_id)
    )"
);

$campus_permissions = [
    "dashboard.view" => "View the admin dashboard",
    "users.verify" => "Review and update student verification",
    "products.moderate" => "Approve or reject product listings",
    "products.feature" => "Feature or unfeature products",
    "reports.review" => "Review reports and act on reported products",
    "payments.review" => "Approve or reject payment records",
    "notices.manage" => "Create and delete notices",
    "roles.manage" => "Manage admin roles and permissions"
];

foreach($campus_permissions as $permission_name => $permission_description){
    campus_seed_permission($conn, $permission_name, $permission_description);
}

$super_role_name = mysqli_real_escape_string($conn, "Super Admin");
$super_role_description = mysqli_real_escape_string(
    $conn,
    "Full access to every admin permission"
);

mysqli_query(
    $conn,
    "INSERT INTO roles(name, description)
     VALUES('$super_role_name', '$super_role_description')
     ON DUPLICATE KEY UPDATE description=VALUES(description)"
);

$super_role_result = mysqli_query(
    $conn,
    "SELECT id FROM roles WHERE name='$super_role_name' LIMIT 1"
);
$super_role = $super_role_result ? mysqli_fetch_assoc($super_role_result) : null;

if($super_role){
    $super_role_id = (int)$super_role['id'];
    mysqli_query(
        $conn,
        "INSERT IGNORE INTO role_permissions(role_id, permission_id)
         SELECT '$super_role_id', id FROM permissions"
    );
}

$admin_check = mysqli_query($conn, "SELECT id FROM admins LIMIT 1");

if($admin_check && mysqli_num_rows($admin_check) == 0){
    $default_admin_password = password_hash("admin123", PASSWORD_DEFAULT);

    mysqli_query(
        $conn,
        "INSERT INTO admins(username,password)
         VALUES('admin','$default_admin_password')"
    );
}

if($super_role){
    $super_role_id = (int)$super_role['id'];
    mysqli_query(
        $conn,
        "INSERT IGNORE INTO admin_roles(admin_id, role_id)
         SELECT id, '$super_role_id' FROM admins
         WHERE id NOT IN (SELECT admin_id FROM admin_roles)"
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

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX token_hash_index (token_hash)
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS email_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX email_token_hash_index (token_hash)
    )"
);

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS login_otps (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        otp_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        attempts INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX login_otp_user_index (user_id),
        INDEX login_otp_hash_index (otp_hash)
    )"
);
?>
