<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$message = "";
$uploadDir = "uploads/";
$serverUploadDir = __DIR__ . "/uploads/";

if(isset($_POST['update_profile'])){
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $roll_number = mysqli_real_escape_string($conn, trim($_POST['roll_number']));
    $semester = mysqli_real_escape_string($conn, trim($_POST['semester']));
    $course = mysqli_real_escape_string($conn, trim($_POST['course']));
    $college_name = mysqli_real_escape_string($conn, trim($_POST['college_name']));

    $current_sql = "SELECT profile_photo, college_id_card, verification_status
                    FROM users
                    WHERE id='$user_id'";
    $current_result = mysqli_query($conn, $current_sql);
    $current_user = mysqli_fetch_assoc($current_result);

    $profile_photo = $current_user['profile_photo'];
    $college_id_card = $current_user['college_id_card'];
    $verification_status = $current_user['verification_status'];

    $allowedProfile = ["jpg", "jpeg", "png"];
    $allowedIdCard = ["jpg", "jpeg", "png", "pdf"];

    if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0){
        $profile_photo_name = time()."_profile_".basename($_FILES['profile_photo']['name']);
        $profile_ext = strtolower(pathinfo($profile_photo_name, PATHINFO_EXTENSION));

        if(!in_array($profile_ext, $allowedProfile)){
            die("Invalid Profile Photo Type");
        }

        if(move_uploaded_file($_FILES['profile_photo']['tmp_name'], $serverUploadDir.$profile_photo_name)){
            $profile_photo = $profile_photo_name;
        }
    }

    if(isset($_FILES['college_id_card']) && $_FILES['college_id_card']['error'] == 0){
        $college_id_name = time()."_college_id_".basename($_FILES['college_id_card']['name']);
        $college_id_ext = strtolower(pathinfo($college_id_name, PATHINFO_EXTENSION));

        if(!in_array($college_id_ext, $allowedIdCard)){
            die("Invalid College ID Card Type");
        }

        if(move_uploaded_file($_FILES['college_id_card']['tmp_name'], $serverUploadDir.$college_id_name)){
            $college_id_card = $college_id_name;
            $verification_status = "Pending";
        }
    }

    $profile_photo = mysqli_real_escape_string($conn, $profile_photo);
    $college_id_card = mysqli_real_escape_string($conn, $college_id_card);
    $verification_status = mysqli_real_escape_string($conn, $verification_status);

    $update_sql = "UPDATE users
                   SET
                   fullname='$fullname',
                   profile_photo='$profile_photo',
                   roll_number='$roll_number',
                   semester='$semester',
                   course='$course',
                   college_name='$college_name',
                   college_id_card='$college_id_card',
                   verification_status='$verification_status'
                   WHERE id='$user_id'";

    if(mysqli_query($conn, $update_sql)){
        $_SESSION['fullname'] = $fullname;
        $message = "Profile updated successfully.";
    }else{
        $message = "Profile update failed: " . mysqli_error($conn);
    }
}

$user_sql = "SELECT * FROM users WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

$product_sql = "SELECT COUNT(*) AS total_products
                FROM products
                WHERE seller_id='$user_id'";

$product_result = mysqli_query($conn, $product_sql);
$product_count = mysqli_fetch_assoc($product_result);

$verificationClass = "not-submitted";

if($user['verification_status'] == "Pending"){
    $verificationClass = "pending";
}
elseif($user['verification_status'] == "Approved"){
    $verificationClass = "approved";
}
elseif($user['verification_status'] == "Rejected"){
    $verificationClass = "rejected";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - Campus Market</title>
<link rel="stylesheet" href="css/style.css">

<style>
body{
    background:#f4f7fc;
    font-family:Arial, sans-serif;
}

.profile-wrap{
    width:950px;
    max-width:95%;
    margin:35px auto;
    display:grid;
    grid-template-columns:300px 1fr;
    gap:20px;
}

.profile-card,
.profile-form{
    background:white;
    padding:24px;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.profile-card{
    text-align:center;
}

.avatar{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    background:#e9ecef;
    margin-bottom:15px;
}

.avatar-placeholder{
    width:130px;
    height:130px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#0d6efd;
    color:white;
    font-size:48px;
    font-weight:bold;
    margin:0 auto 15px;
}

.info{
    background:#f8f9fa;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    text-align:left;
}

.status-pill{
    display:inline-block;
    padding:7px 12px;
    border-radius:20px;
    color:white;
    font-size:13px;
    margin-top:10px;
}

.not-submitted{
    background:#6c757d;
}

.pending{
    background:#ffc107;
    color:#111;
}

.approved{
    background:#198754;
}

.rejected{
    background:#dc3545;
}

.profile-form h2{
    margin-bottom:15px;
}

.profile-form label{
    display:block;
    margin-top:12px;
    font-weight:bold;
}

.profile-form input,
.profile-form select{
    width:100%;
    padding:12px;
    margin-top:7px;
    border:1px solid #ddd;
    border-radius:6px;
}

.current-file{
    color:#555;
    font-size:14px;
    margin-top:6px;
}

.message{
    padding:12px;
    background:#e7f3ff;
    color:#084298;
    border-radius:8px;
    margin-bottom:15px;
}

.btn-row{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:18px;
}

.btn{
    display:inline-block;
    padding:12px 18px;
    text-decoration:none;
    color:white;
    border-radius:6px;
    border:none;
    cursor:pointer;
}

.save-btn{
    background:#0d6efd;
}

.dashboard-btn{
    background:#6f42c1;
}

.logout-btn{
    background:#dc3545;
}

@media(max-width:768px){
    .profile-wrap{
        grid-template-columns:1fr;
    }
}
</style>

</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="profile-wrap">

    <div class="profile-card">
        <h1>My Profile</h1>

        <?php if(!empty($user['profile_photo'])){ ?>
            <img
            class="avatar"
            src="<?php echo $uploadDir.htmlspecialchars($user['profile_photo']); ?>"
            alt="Profile photo">
        <?php }else{ ?>
            <div class="avatar-placeholder">
                <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
            </div>
        <?php } ?>

        <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
        <p><?php echo htmlspecialchars($user['email']); ?></p>

        <span class="status-pill <?php echo $verificationClass; ?>">
            <?php echo htmlspecialchars($user['verification_status']); ?>
        </span>

        <div class="info">
            <strong>Total Products:</strong>
            <?php echo (int)$product_count['total_products']; ?>
        </div>

        <div class="info">
            <strong>Roll Number:</strong>
            <?php echo htmlspecialchars($user['roll_number']); ?>
        </div>

        <div class="info">
            <strong>Semester:</strong>
            <?php echo htmlspecialchars($user['semester']); ?>
        </div>

        <div class="info">
            <strong>Course:</strong>
            <?php echo htmlspecialchars($user['course']); ?>
        </div>

        <div class="info">
            <strong>College:</strong>
            <?php echo htmlspecialchars($user['college_name']); ?>
        </div>

        <div class="btn-row">
            <a href="dashboard.php" class="btn dashboard-btn">Dashboard</a>
            <a href="../backend/logout.php" class="btn logout-btn">Logout</a>
        </div>
    </div>

    <div class="profile-form">
        <h2>Edit Profile</h2>

        <?php if(!empty($message)){ ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Full Name</label>
            <input
            type="text"
            name="fullname"
            value="<?php echo htmlspecialchars($user['fullname']); ?>"
            required>

            <label>Email</label>
            <input
            type="email"
            value="<?php echo htmlspecialchars($user['email']); ?>"
            readonly>

            <label>Roll Number</label>
            <input
            type="text"
            name="roll_number"
            value="<?php echo htmlspecialchars($user['roll_number']); ?>">

            <label>Semester</label>
            <select name="semester">
                <option value="">Select Semester</option>
                <?php for($i = 1; $i <= 8; $i++){ ?>
                    <option value="<?php echo $i; ?>" <?php if($user['semester'] == $i) echo "selected"; ?>>
                        Semester <?php echo $i; ?>
                    </option>
                <?php } ?>
            </select>

            <label>Course</label>
            <input
            type="text"
            name="course"
            value="<?php echo htmlspecialchars($user['course']); ?>"
            placeholder="BCA, B.Tech, MBA">

            <label>College Name</label>
            <input
            type="text"
            name="college_name"
            value="<?php echo htmlspecialchars($user['college_name']); ?>">

            <label>Profile Photo</label>
            <input
            type="file"
            name="profile_photo"
            accept="image/*">
            <div class="current-file">
                Current: <?php echo !empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : "No photo uploaded"; ?>
            </div>

            <label>College ID Card</label>
            <input
            type="file"
            name="college_id_card"
            accept=".jpg,.jpeg,.png,.pdf">
            <div class="current-file">
                Current: <?php echo !empty($user['college_id_card']) ? htmlspecialchars($user['college_id_card']) : "No ID card uploaded"; ?>
            </div>

            <?php if(!empty($user['college_id_card'])){ ?>
                <a
                href="uploads/<?php echo htmlspecialchars($user['college_id_card']); ?>"
                target="_blank">
                    View Uploaded ID Card
                </a>
            <?php } ?>

            <div class="btn-row">
                <button
                type="submit"
                name="update_profile"
                class="btn save-btn">
                    Save Profile
                </button>
            </div>
        </form>
    </div>

</div>

</body>
</html>
