<?php
// File này được include từ baiviet.php (đã có session_start, $conn, $id ở trên)
$id_baiviet = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_POST['gui'])) {
    $ten = trim($_POST['ten']);
    $noidung = trim($_POST['noidung']);

    if ($ten !== '' && $noidung !== '' && $id_baiviet > 0) {
        $stmt = $conn->prepare("
            INSERT INTO binhluan (id_baiviet, ten_docgia, noi_dung, trang_thai)
            VALUES (?, ?, ?, 1)
        ");
        $stmt->bind_param('iss', $id_baiviet, $ten, $noidung);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: baiviet.php?id=" . $id_baiviet);
    exit();
}
?>
<div class="khung-binhluan">
<h3>Bình luận</h3>
<form method="POST">
<input type="text" name="ten" placeholder="Nhập tên" required>
<textarea name="noidung" placeholder="Nhập bình luận..." required></textarea>
<button type="submit" name="gui">Gửi bình luận</button>
</form>
<hr>
<?php
$stmt = $conn->prepare("
    SELECT * FROM binhluan
    WHERE id_baiviet = ? AND trang_thai = 1
    ORDER BY ngay_dang DESC
");
$stmt->bind_param('i', $id_baiviet);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
?>
<div class="comment">
<strong><?= htmlspecialchars($row['ten_docgia']) ?></strong>
<p><?= htmlspecialchars($row['noi_dung']) ?></p>
<small><?= $row['ngay_dang'] ?></small>
</div>
<?php endwhile; $stmt->close(); ?>
</div>
