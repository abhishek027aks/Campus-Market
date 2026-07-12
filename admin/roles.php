<?php
session_start();
include "../backend/config.php";

campus_require_admin_permission($conn, "roles.manage");

$message = "";

if(isset($_POST['create_role'])){
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if($name !== ""){
        $safe_name = mysqli_real_escape_string($conn, $name);
        $safe_description = mysqli_real_escape_string($conn, $description);

        mysqli_query(
            $conn,
            "INSERT INTO roles(name, description)
             VALUES('$safe_name', '$safe_description')
             ON DUPLICATE KEY UPDATE description=VALUES(description)"
        );

        $message = "Role saved.";
    }
}

if(isset($_POST['assign_admin_role'])){
    $admin_id = (int)$_POST['admin_id'];
    $role_id = (int)$_POST['role_id'];

    if($admin_id > 0 && $role_id > 0){
        mysqli_query(
            $conn,
            "INSERT IGNORE INTO admin_roles(admin_id, role_id)
             VALUES('$admin_id', '$role_id')"
        );

        $message = "Admin role assigned.";
    }
}

if(isset($_POST['remove_admin_role'])){
    $admin_id = (int)$_POST['admin_id'];
    $role_id = (int)$_POST['role_id'];

    mysqli_query(
        $conn,
        "DELETE FROM admin_roles
         WHERE admin_id='$admin_id'
         AND role_id='$role_id'"
    );

    $message = "Admin role removed.";
}

$roles = mysqli_query(
    $conn,
    "SELECT roles.*,
        COUNT(DISTINCT role_permissions.permission_id) AS permission_count,
        COUNT(DISTINCT admin_roles.admin_id) AS admin_count
     FROM roles
     LEFT JOIN role_permissions ON roles.id = role_permissions.role_id
     LEFT JOIN admin_roles ON roles.id = admin_roles.role_id
     GROUP BY roles.id
     ORDER BY roles.name ASC"
);

$admins = mysqli_query($conn, "SELECT id, username FROM admins ORDER BY username ASC");
$admin_roles = mysqli_query(
    $conn,
    "SELECT admin_roles.admin_id, admin_roles.role_id, admins.username, roles.name AS role_name
     FROM admin_roles
     JOIN admins ON admin_roles.admin_id = admins.id
     JOIN roles ON admin_roles.role_id = roles.id
     ORDER BY admins.username ASC, roles.name ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Roles - Admin</title>
<link rel="stylesheet" href="../frontend/css/style.css">
<style>
body{background:#f4f7fc;color:#1f2937;font-family:Arial,sans-serif}
.admin-wrap{width:1150px;max-width:96%;margin:30px auto}
.topbar,.panel{background:white;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.08);padding:18px;margin-bottom:18px}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:12px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
table{width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
th{background:#0d6efd;color:white}
.btn-small{display:inline-block;padding:8px 10px;color:white;text-decoration:none;border-radius:5px;margin:3px 0;font-size:13px;border:0;cursor:pointer}
.back{background:#0d6efd}.success{background:#198754}.danger{background:#dc3545}
.message{background:#d1e7dd;color:#0f5132;padding:12px;border-radius:6px;margin-bottom:15px}
select,input,textarea{width:100%;box-sizing:border-box;margin-bottom:10px}
@media(max-width:850px){.grid{grid-template-columns:1fr}.topbar{flex-direction:column;align-items:flex-start}table{display:block;overflow-x:auto}}
</style>
</head>
<body>

<div class="admin-wrap">
    <div class="topbar">
        <div>
            <h1>Roles & Permissions</h1>
            <p>Create configurable roles and assign access without code changes.</p>
        </div>
        <a class="btn-small back" href="dashboard.php">Dashboard</a>
    </div>

    <?php if($message !== ""){ ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <div class="grid">
        <section class="panel">
            <h2>Create Role</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Role Name" required>
                <textarea name="description" placeholder="Description"></textarea>
                <button class="btn-small success" type="submit" name="create_role">Save Role</button>
            </form>
        </section>

        <section class="panel">
            <h2>Assign Role To Admin</h2>
            <form method="POST">
                <select name="admin_id" required>
                    <option value="">Select Admin</option>
                    <?php while($admin = mysqli_fetch_assoc($admins)){ ?>
                        <option value="<?php echo (int)$admin['id']; ?>">
                            <?php echo htmlspecialchars($admin['username']); ?>
                        </option>
                    <?php } ?>
                </select>

                <select name="role_id" required>
                    <option value="">Select Role</option>
                    <?php
                    mysqli_data_seek($roles, 0);
                    while($role = mysqli_fetch_assoc($roles)){
                    ?>
                        <option value="<?php echo (int)$role['id']; ?>">
                            <?php echo htmlspecialchars($role['name']); ?>
                        </option>
                    <?php } ?>
                </select>

                <button class="btn-small success" type="submit" name="assign_admin_role">Assign Role</button>
            </form>
        </section>
    </div>

    <section class="panel">
        <h2>Role List</h2>
        <table>
            <tr>
                <th>Role</th>
                <th>Description</th>
                <th>Permissions</th>
                <th>Admins</th>
                <th>Action</th>
            </tr>
            <?php
            mysqli_data_seek($roles, 0);
            while($role = mysqli_fetch_assoc($roles)){
            ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($role['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($role['description']); ?></td>
                    <td><?php echo (int)$role['permission_count']; ?></td>
                    <td><?php echo (int)$role['admin_count']; ?></td>
                    <td>
                        <a class="btn-small back" href="role-permissions.php?id=<?php echo (int)$role['id']; ?>">
                            Manage Permissions
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </section>

    <section class="panel">
        <h2>Admin Role Assignments</h2>
        <table>
            <tr>
                <th>Admin</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
            <?php while($assignment = mysqli_fetch_assoc($admin_roles)){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($assignment['username']); ?></td>
                    <td><?php echo htmlspecialchars($assignment['role_name']); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="admin_id" value="<?php echo (int)$assignment['admin_id']; ?>">
                            <input type="hidden" name="role_id" value="<?php echo (int)$assignment['role_id']; ?>">
                            <button class="btn-small danger" type="submit" name="remove_admin_role">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </section>
</div>

</body>
</html>
