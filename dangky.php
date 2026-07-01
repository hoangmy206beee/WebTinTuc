<?php
session_start();
include('config/ketnoi.php');

$thongbao = "";

if (isset($_POST['dangky'])) {
    $tendangnhap = trim($_POST['tendangnhap']);
    $matkhau = $_POST['matkhau'];
    $nhaplaimatkhau = $_POST['nhaplaimatkhau'];

    if (empty($tendangnhap) || empty($matkhau)) {
        $thongbao = "Vui lòng nhập đầy đủ thông tin!";
    } elseif ($matkhau != $nhaplaimatkhau) {
        $thongbao = "Mật khẩu nhập lại không khớp!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM nguoidung WHERE tendangnhap = ?");
        $stmt->bind_param("s", $tendangnhap);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $thongbao = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác!";
        } else {
            $matkhau_mahoa = password_hash($matkhau, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO nguoidung (tendangnhap, matkhau) VALUES (?, ?)");
            $stmt2->bind_param("ss", $tendangnhap, $matkhau_mahoa);

            if ($stmt2->execute()) {
                echo "<script>alert('Đăng ký thành công!'); window.location.href='dangnhap.php';</script>";
                exit;
            } else {
                $thongbao = "Lỗi: " . $conn->error;
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; margin-top: 50px; }
        .form-container { border: 1px solid #ccc; padding: 20px; width: 300px; border-radius: 5px; }
        input[type="text"], input[type="password"] { width: 90%; padding: 10px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; cursor: pointer; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Đăng ký</h2>
    <?php if ($thongbao != "") echo "<div class='error'>" . htmlspecialchars($thongbao) . "</div>"; ?>
    <form action="" method="POST">
        <label>Tên đăng nhập:</label>
        <input type="text" name="tendangnhap" required>

        <label>Mật khẩu:</label>
        <input type="password" name="matkhau" required>

        <label>Nhập lại mật khẩu:</label>
        <input type="password" name="nhaplaimatkhau" required>

        <button type="submit" name="dangky">Đăng ký</button>
        <p style="text-align: center;"><a href="dangnhap.php">Đã có tài khoản? Đăng nhập</a></p>
    </form>
</div>

</body>
</html>
