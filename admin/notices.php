<?php
session_start();
include "../backend/config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$allowed_categories = ["Exam", "Placement", "College Update"];
$error = "";

if(isset($_POST['add_notice'])){
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $category = $_POST['category'];
    $notice_date = mysqli_real_escape_string($conn, $_POST['notice_date']);
    $admin_id = (int)$_SESSION['admin_id'];

    if(!in_array($category, $allowed_categories, true)){
        $error = "Invalid notice category";
    }elseif($title === "" || $description === ""){
        $error = "Title and description are required";
    }else{
        $safe_category = mysqli_real_escape_string($conn, $category);
        $date_value = $notice_date !== "" ? "'$notice_date'" : "NULL";

        mysqli_query(
            $conn,
            "INSERT INTO notices(admin_id, title, description, category, notice_date)
             VALUES('$admin_id', '$title', '$description', '$safe_category', $date_value)"
        );

        header("Location: notices.php");
        exit();
    }
}

if(isset($_POST['delete_notice'])){
    $id = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM notices WHERE id='$id'");
    header("Location: notices.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Notices - Admin</title>
<link rel="stylesheet" href="../frontend/css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.admin-wrap{width:1100px;max-width:95%;margin:30px auto}
.topbar,.form-box,table{background:#fff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08)}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:18px;margin-bottom:18px}
.form-box{padding:18px;margin-bottom:18px}
label{display:block;font-weight:bold;margin-top:10px}
input,select,textarea{width:100%;padding:10px;margin-top:6px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box}
textarea{min-height:110px;resize:vertical}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
th{background:#0d6efd;color:#fff}
.btn{display:inline-block;padding:10px 14px;border:0;border-radius:6px;color:#fff;text-decoration:none;cursor:pointer;margin-top:10px}
.back,.save{background:#0d6efd}.delete{background:#dc3545}
.badge{display:inline-block;padding:6px 10px;border-radius:20px;color:#fff;font-size:13px}
.exam{background:#6f42c1}.placement{background:#198754}.college{background:#fd7e14;color:#111}
.error{background:#f8d7da;color:#842029;padding:10px;border-radius:6px;margin-bottom:12px}
@media(max-width:800px){table{display:block;overflow-x:auto}.topbar{align-items:flex-start;flex-direction:column;gap:10px}}
</style>
</head>
<body>
<main class="admin-wrap">
    <div class="topbar">
        <div>
            <h1>Notice Board</h1>
            <p>Create exam, placement and college update notices.</p>
        </div>
        <a class="btn back" href="dashboard.php">Dashboard</a>
    </div>

    <section class="form-box">
        <h2>Add Notice</h2>

        <?php if($error !== ""){ ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST">
            <label>Category</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <?php foreach($allowed_categories as $category){ ?>
                    <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                <?php } ?>
            </select>

            <label>Title</label>
            <input type="text" name="title" placeholder="Notice title" required>

            <label>Description</label>
            <textarea name="description" placeholder="Notice details" required></textarea>

            <label>Notice Date Optional</label>
            <input type="date" name="notice_date">

            <button class="btn save" type="submit" name="add_notice">Publish Notice</button>
        </form>
    </section>

    <table>
        <tr><th>Notice</th><th>Category</th><th>Date</th><th>Action</th></tr>
        <?php if($result && mysqli_num_rows($result) > 0){ ?>
            <?php while($notice = mysqli_fetch_assoc($result)){ ?>
                <?php
                $category_class = strtolower(str_replace(" Update", "", $notice['category']));
                ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($notice['title']); ?></strong>
                        <p><?php echo nl2br(htmlspecialchars($notice['description'])); ?></p>
                    </td>
                    <td><span class="badge <?php echo htmlspecialchars($category_class); ?>"><?php echo htmlspecialchars($notice['category']); ?></span></td>
                    <td>
                        <?php
                        echo !empty($notice['notice_date'])
                            ? date("d M Y", strtotime($notice['notice_date']))
                            : date("d M Y", strtotime($notice['created_at']));
                        ?>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo (int)$notice['id']; ?>">
                            <button class="btn delete" type="submit" name="delete_notice">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        <?php }else{ ?>
            <tr><td colspan="4">No notices yet.</td></tr>
        <?php } ?>
    </table>
</main>
</body>
</html>
