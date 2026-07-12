<?php
session_start();
include "../backend/config.php";

campus_require_admin_permission($conn, "roles.manage");

$role_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$role_result = mysqli_query($conn, "SELECT * FROM roles WHERE id='$role_id' LIMIT 1");
$role = $role_result ? mysqli_fetch_assoc($role_result) : null;

if(!$role){
    die("Role not found");
}

if(isset($_POST['save_permissions'])){
    mysqli_query($conn, "DELETE FROM role_permissions WHERE role_id='$role_id'");

    if(isset($_POST['permissions']) && is_array($_POST['permissions'])){
        foreach($_POST['permissions'] as $permission_id){
            $permission_id = (int)$permission_id;

            if($permission_id > 0){
                mysqli_query(
                    $conn,
                    "INSERT IGNORE INTO role_permissions(role_id, permission_id)
                     VALUES('$role_id', '$permission_id')"
                );
            }
        }
    }

    header("Location: role-permissions.php?id=$role_id&saved=1");
    exit();
}

$selected = [];
$selected_result = mysqli_query($conn, "SELECT permission_id FROM role_permissions WHERE role_id='$role_id'");

while($selected_row = mysqli_fetch_assoc($selected_result)){
    $selected[] = (int)$selected_row['permission_id'];
}

$permissions = mysqli_query($conn, "SELECT * FROM permissions ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Role Permissions - Admin</title>
<link rel="stylesheet" href="../frontend/css/style.css">
<style>
body{background:#f4f7fc;color:#1f2937;font-family:Arial,sans-serif}
.admin-wrap{width:900px;max-width:96%;margin:30px auto}
.topbar,.panel{background:white;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.08);padding:18px;margin-bottom:18px}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:12px}
.permission-row{display:flex;align-items:flex-start;gap:10px;padding:12px;border-bottom:1px solid #eee}
.permission-row input{width:auto;margin-top:3px}
.btn-small{display:inline-block;padding:9px 12px;color:white;text-decoration:none;border-radius:5px;margin:3px 0;font-size:13px;border:0;cursor:pointer}
.back{background:#0d6efd}.success{background:#198754}
.message{background:#d1e7dd;color:#0f5132;padding:12px;border-radius:6px;margin-bottom:15px}
@media(max-width:700px){.topbar{flex-direction:column;align-items:flex-start}}
</style>
</head>
<body>

<div class="admin-wrap">
    <div class="topbar">
        <div>
            <h1><?php echo htmlspecialchars($role['name']); ?></h1>
            <p><?php echo htmlspecialchars($role['description']); ?></p>
        </div>
        <a class="btn-small back" href="roles.php">Back To Roles</a>
    </div>

    <?php if(isset($_GET['saved'])){ ?>
        <div class="message">Permissions updated.</div>
    <?php } ?>

    <section class="panel">
        <h2>Permissions</h2>
        <form method="POST">
            <?php while($permission = mysqli_fetch_assoc($permissions)){ ?>
                <label class="permission-row">
                    <input
                    type="checkbox"
                    name="permissions[]"
                    value="<?php echo (int)$permission['id']; ?>"
                    <?php if(in_array((int)$permission['id'], $selected, true)) echo "checked"; ?>>

                    <span>
                        <strong><?php echo htmlspecialchars($permission['name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($permission['description']); ?></small>
                    </span>
                </label>
            <?php } ?>

            <button class="btn-small success" type="submit" name="save_permissions">Save Permissions</button>
        </form>
    </section>
</div>

</body>
</html>
