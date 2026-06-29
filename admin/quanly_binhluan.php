<?php
session_start();
require_once '../config/ketnoi.php';
// Xóa
if(isset($_GET['xoa'])){
$id=(int)$_GET['xoa'];
$stmt=$conn->prepare(
"DELETE FROM binhluan WHERE id=?"
);
$stmt->bind_param(
"i",
$id
);
$stmt->execute();
header(
"Location: quanlybinhluan.php"
);
exit;
}
// Lấy dữ liệu
$sql="
SELECT
b.*,
p.title
FROM binhluan b
LEFT JOIN posts p
ON b.id_baiviet=p.id
ORDER BY b.ngay_dang DESC
";
$result=
$conn
->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>
Quản lý bình luận
</title>
<link
rel="stylesheet"
href="../style.css">
</head>
<body>
<div class="admin-content">
<h2>
Quản lý bình luận
</h2>
<table class="table">
<tr>
<th>ID</th>
<th>Bài viết</th>
<th>Người gửi</th>
<th>Bình luận</th>
<th>Ngày</th>
<th>Thao tác</th>
</tr>
<?php while(
$row=
$result->fetch_assoc()
): ?>
<tr>
<td>
<?= $row['id'] ?>
</td>
<td>
<?= htmlspecialchars($row['title']) ?>
</td>
<td>
<?= htmlspecialchars($row['ten_docgia']) ?>
</td>
<td>
<?= htmlspecialchars($row['noi_dung']) ?>
</td>
<td>
<?= $row['ngay_dang'] ?>
</td>
<td>
<a
class="btn btn-danger"
onclick=
"return confirm('Xóa?')"
href=
"?xoa=<?= $row['id'] ?>">
Xóa
</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>