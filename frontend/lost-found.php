<?php
include "../backend/config.php";

$search = "";
$type = "";

$sql = "SELECT lost_found.*, users.fullname
        FROM lost_found
        JOIN users ON lost_found.user_id = users.id
        WHERE 1=1";

if(isset($_GET['search']) && trim($_GET['search']) !== ""){
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
    $sql .= " AND (
        lost_found.title LIKE '%$search%'
        OR lost_found.description LIKE '%$search%'
        OR lost_found.location LIKE '%$search%'
    )";
}

if(isset($_GET['type']) && $_GET['type'] !== ""){
    $type = mysqli_real_escape_string($conn, $_GET['type']);
    $sql .= " AND lost_found.item_type='$type'";
}

$sql .= " ORDER BY lost_found.id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lost & Found - Campus Market</title>
<link rel="stylesheet" href="css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.page{width:1100px;max-width:95%;margin:30px auto}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.search-box{background:#fff;padding:16px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08);margin:18px 0;text-align:center}
.search-box input,.search-box select,.search-box button{padding:10px;margin:5px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px}
.card{background:#fff;padding:16px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08)}
.item-img{width:100%;height:180px;object-fit:cover;border-radius:8px;background:#e9ecef}
.badge{display:inline-block;padding:6px 10px;border-radius:20px;color:#fff;font-size:13px;margin:6px 0}
.lost{background:#dc3545}.found{background:#198754}.open{background:#0d6efd}
.btn{display:inline-block;padding:10px 14px;border-radius:6px;background:#0d6efd;color:#fff;text-decoration:none;border:0;cursor:pointer}
.muted{color:#666;font-size:14px}
</style>
</head>
<body>
<?php include "includes/navbar.php"; ?>
<main class="page">
    <div class="topbar">
        <div>
            <h1>Lost & Found</h1>
            <p class="muted">Post and search campus lost/found items.</p>
        </div>
        <div>
            <a class="btn" href="post-lost-found.php">Post Item</a>
            <a class="btn" href="dashboard.php">Dashboard</a>
        </div>
    </div>

    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search item or location" value="<?php echo htmlspecialchars($search); ?>">
            <select name="type">
                <option value="">All Types</option>
                <option value="Lost" <?php if($type === "Lost") echo "selected"; ?>>Lost</option>
                <option value="Found" <?php if($type === "Found") echo "selected"; ?>>Found</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="grid">
        <?php if($result && mysqli_num_rows($result) > 0){ ?>
            <?php while($item = mysqli_fetch_assoc($result)){ ?>
                <article class="card">
                    <?php if(!empty($item['image'])){ ?>
                        <img class="item-img" src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <?php }else{ ?>
                        <div class="item-img"></div>
                    <?php } ?>

                    <span class="badge <?php echo strtolower(htmlspecialchars($item['item_type'])); ?>">
                        <?php echo htmlspecialchars($item['item_type']); ?>
                    </span>
                    <span class="badge open"><?php echo htmlspecialchars($item['status']); ?></span>

                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                    <p><b>Location:</b> <?php echo htmlspecialchars($item['location']); ?></p>
                    <p><b>Contact:</b> <?php echo htmlspecialchars($item['contact']); ?></p>
                    <p class="muted">
                        Posted by <?php echo htmlspecialchars($item['fullname']); ?>
                        on <?php echo date("d M Y", strtotime($item['created_at'])); ?>
                    </p>
                </article>
            <?php } ?>
        <?php }else{ ?>
            <p>No lost/found posts yet.</p>
        <?php } ?>
    </div>
</main>
</body>
</html>
