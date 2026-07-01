<?php
session_start();
include('config/ketnoi.php');

$thongbao = "";

if (isset($_POST['dangnhap'])) {
    $tendangnhap = trim($_POST['tendangnhap']);
    $matkhau = $_POST['matkhau'];

    if (empty($tendangnhap) || empty($matkhau)) {
        $thongbao = "Vui lòng nhập tài khoản và mật khẩu!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM nguoidung WHERE tendangnhap = ?");
        $stmt->bind_param("s", $tendangnhap);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($matkhau, $row['matkhau'])) {
                $_SESSION['tendangnhap'] = $tendangnhap;
                $_SESSION['id_nguoidung'] = $row['id'];

                header("Location: index.php");
                exit();
            } else {
                $thongbao = "Sai mật khẩu!";
            }
        } else {
            $thongbao = "Tài khoản không tồn tại!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; margin-top: 50px; }
        .form-container { border: 1px solid #ccc; padding: 20px; width: 300px; border-radius: 5px; }
        input[type="text"], input[type="password"] { width: 90%; padding: 10px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Đăng nhập</h2>
    <?php if ($thongbao != "") echo "<div class='error'>" . htmlspecialchars($thongbao) . "</div>"; ?>
    <form action="" method="POST">
        <label>Tên đăng nhập:</label>
        <input type="text" name="tendangnhap" required>

        <label>Mật khẩu:</label>
        <input type="password" name="matkhau" required>

        <button type="submit" name="dangnhap">Đăng nhập</button>
        <p style="text-align: center;"><a href="dangky.php">Chưa có tài khoản? Đăng ký</a></p>
    </form>
</div>

</body>
</html>
