<?php
include "config.php";

if(!isset($_GET['id'])){
    die("File Not Found");
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, image, title, approval_status
     FROM products
     WHERE id=?"
);
$result = false;
if($stmt){
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
$product = $result ? mysqli_fetch_assoc($result) : null;

if(!$product || empty($product['image'])){
    die("File Not Found");
}

$file_name = basename($product['image']);
$file_path = __DIR__ . "/../frontend/uploads/" . $file_name;

if(!is_file($file_path)){
    die("File Not Found");
}

if($product['approval_status'] == "Approved"){
    $download_stmt = mysqli_prepare(
        $conn,
        "UPDATE products SET downloads = downloads + 1 WHERE id=?"
    );
    mysqli_stmt_bind_param($download_stmt, "i", $id);
    mysqli_stmt_execute($download_stmt);
}

header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . basename($file_name) . "\"");
header("Content-Length: " . filesize($file_path));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: public");

readfile($file_path);
exit();
?>
