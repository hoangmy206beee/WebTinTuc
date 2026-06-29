<?php
include("config/ketnoi.php");
$id_baiviet = $_GET['id'];
// Gửi bình luận
if(isset($_POST['gui'])){
$ten = $_POST['ten'];
$noidung = $_POST['noidung'];
$sql = "
INSERT INTO binhluan
(id_baiviet, ten_docgia, noi_dung, trang_thai)
VALUES
('$id_baiviet','$ten','$noidung',1)
";
mysqli_query($conn,$sql);
// load lại trang
header("Location: baiviet.php?id=$id_baiviet");
exit();
}
?>
<div class="khung-binhluan">
<h3>Bình luận</h3>
<form method="POST">
<input
type="text"
name="ten"
placeholder="Nhập tên"
required>
<textarea
name="noidung"
placeholder="Nhập bình luận..."
required>
</textarea>
<button type="submit" name="gui">
Gửi bình luận
</button>
</form>
<hr>
<?php
$sql="
SELECT *
FROM binhluan
WHERE id_baiviet='$id_baiviet'
ORDER BY ngay_dang DESC
";
$result=mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){
?>
<div class="comment">
<strong>
<?= $row['ten_docgia'] ?>
</strong>
<p>
<?= $row['noi_dung'] ?>
</p>
<small>
<?= $row['ngay_dang'] ?>
</small>
</div>
<?php } ?>
</div>
