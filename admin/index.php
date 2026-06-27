<?php
session_start();

if(!isset($_SESSION['admin'])){
    $_SESSION['admin']="Admin";
}

$tongTin = 6;
$tongDanhMuc = 5;
$tongLuotXem = 33000;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Tin Tức 24H</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<header class="header">

    <div class="logo">
        ADMIN - TIN TỨC 24H
    </div>

    <div class="avatar">
        Xin chào,
        <b><?php echo $_SESSION['admin']; ?></b>
    </div>

</header>

<nav class="menu">

    <a href="../index.php">Trang chủ</a>

    <a href="#">Quản lý tin tức</a>

    <a href="#">Quản lý danh mục</a>

    <a href="#">Đăng xuất</a>

</nav>

<section class="section">

    <h2>THỐNG KÊ</h2>

    <div class="dashboard">

        <div class="box">

            <h3><?php echo $tongTin; ?></h3>

            <p>Tổng bài viết</p>

        </div>

        <div class="box">

            <h3><?php echo $tongDanhMuc; ?></h3>

            <p>Danh mục</p>

        </div>

        <div class="box">

            <h3><?php echo number_format($tongLuotXem); ?></h3>

            <p>Lượt xem</p>

        </div>

    </div>

</section>

<section class="section">

    <h2>DANH SÁCH BÀI VIẾT</h2>

    <table class="table">

        <tr>

            <th>STT</th>

            <th>Tiêu đề</th>

            <th>Trạng thái</th>

        </tr>

        <tr>

            <td>1</td>

            <td>Công nghệ AI phát triển mạnh</td>

            <td>Đã đăng</td>

        </tr>

        <tr>

            <td>2</td>

            <td>Kinh tế Việt Nam tăng trưởng</td>

            <td>Đã đăng</td>

        </tr>

        <tr>

            <td>3</td>

            <td>Thể thao cuối tuần</td>

            <td>Đã đăng</td>

        </tr>

    </table>

</section>

<footer class="footer">

    <p>
        © 2026 Admin - Tin Tức 24H
    </p>

</footer>

</body>
</html>
