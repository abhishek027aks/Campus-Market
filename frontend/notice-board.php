<?php
include "../backend/config.php";

$category = "";
$allowed_categories = ["Exam", "Placement", "College Update"];

$sql = "SELECT * FROM notices";

if(isset($_GET['category']) && $_GET['category'] !== ""){
    $category = $_GET['category'];

    if(in_array($category, $allowed_categories, true)){
        $safe_category = mysqli_real_escape_string($conn, $category);
        $sql .= " WHERE category='$safe_category'";
    }else{
        $category = "";
    }
}

$sql .= " ORDER BY COALESCE(notice_date, DATE(created_at)) DESC, id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notice Board - Campus Market</title>
<link rel="stylesheet" href="css/style.css">
<style>
body{background:#f4f7fc;font-family:Arial,sans-serif}
.page{width:1000px;max-width:95%;margin:30px auto}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.filters{background:#fff;padding:16px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08);margin:18px 0;text-align:center}
.filters select,.filters button{padding:10px;margin:5px}
.notice-list{display:grid;gap:16px}
.notice-card{background:#fff;padding:18px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.08);border-left:5px solid #0d6efd}
.notice-head{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
.badge{display:inline-block;padding:6px 10px;border-radius:20px;color:#fff;font-size:13px}
.exam{background:#6f42c1}.placement{background:#198754}.college{background:#fd7e14;color:#111}
.btn{display:inline-block;padding:10px 14px;border-radius:6px;background:#0d6efd;color:#fff;text-decoration:none;border:0;cursor:pointer}
.muted{color:#666;font-size:14px}
</style>
</head>
<body>
<main class="page">
    <div class="topbar">
        <div>
            <h1>Notice Board</h1>
            <p class="muted">Exam notices, placement updates and college announcements.</p>
        </div>
        <a class="btn" href="dashboard.php">Dashboard</a>
    </div>

    <div class="filters">
        <form method="GET">
            <select name="category">
                <option value="">All Notices</option>
                <?php foreach($allowed_categories as $option){ ?>
                    <option value="<?php echo $option; ?>" <?php if($category === $option) echo "selected"; ?>>
                        <?php echo $option; ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <section class="notice-list">
        <?php if($result && mysqli_num_rows($result) > 0){ ?>
            <?php while($notice = mysqli_fetch_assoc($result)){ ?>
                <?php
                $category_class = strtolower(str_replace(" Update", "", $notice['category']));
                $display_date = !empty($notice['notice_date'])
                    ? $notice['notice_date']
                    : $notice['created_at'];
                ?>
                <article class="notice-card">
                    <div class="notice-head">
                        <span class="badge <?php echo htmlspecialchars($category_class); ?>">
                            <?php echo htmlspecialchars($notice['category']); ?>
                        </span>
                        <span class="muted">
                            <?php echo date("d M Y", strtotime($display_date)); ?>
                        </span>
                    </div>

                    <h2><?php echo htmlspecialchars($notice['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($notice['description'])); ?></p>
                </article>
            <?php } ?>
        <?php }else{ ?>
            <p>No notices published yet.</p>
        <?php } ?>
    </section>
</main>
</body>
</html>
