<?php
session_start();

$tinMoi = [
    [
        "anh" => "images/news1.jpg",
        "tieude" => "Công nghệ AI phát triển mạnh",
        "mota" => "Cập nhật những xu hướng công nghệ mới nhất năm 2026, nhiều ứng dụng AI đang được triển khai trong giáo dục, y tế và doanh nghiệp.",
        "time" => "15 phút trước"
    ],
    [
        "anh" => "images/news2.jpg",
        "tieude" => "Kinh tế Việt Nam tăng trưởng",
        "mota" => "Nhiều tín hiệu tích cực từ nền kinh tế trong quý mới, hoạt động sản xuất và xuất khẩu tiếp tục phục hồi.",
        "time" => "1 giờ trước"
    ],
    [
        "anh" => "images/news3.jpg",
        "tieude" => "Thể thao cuối tuần",
        "mota" => "Những trận đấu hấp dẫn được mong chờ nhất, thu hút đông đảo người hâm mộ.",
        "time" => "2 giờ trước"
    ]
];

$tinHot = [
    [
        "anh" => "images/hot1.jpg",
        "tieude" => "Du lịch hè 2026",
        "mota" => "Các điểm đến nổi tiếng trong nước thu hút lượng lớn du khách.",
        "time" => "12.500 lượt xem"
    ],
    [
        "anh" => "images/hot2.jpg",
        "tieude" => "Giá vàng tăng mạnh",
        "mota" => "Thị trường vàng ghi nhận nhiều biến động trong tuần qua.",
        "time" => "10.800 lượt xem"
    ],
    [
        "anh" => "images/hot3.jpg",
        "tieude" => "Xu hướng AI mới",
        "mota" => "Nhiều công nghệ AI thế hệ mới đang được nghiên cứu và phát triển.",
        "time" => "9.700 lượt xem"
    ]
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tin Tức 24H</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">

    <div class="logo">
        TIN TỨC 24H
    </div>

    <div class="search-box">
        <input type="text" id="search" placeholder="Tìm kiếm tin tức..." onkeyup="timKiem()">
    </div>

    <div class="avatar">
        <img src="images/avatar.jpg">
    </div>

</header>

<nav class="menu">
    <a href="#">Trang chủ</a>
    <a href="#">Thời sự</a>
    <a href="#">Công nghệ</a>
    <a href="#">Giải trí</a>
    <a href="#">Thể thao</a>
</nav>

<section class="banner">
    <img src="images/banner.jpg">
</section>

<section class="section">

    <h2>TIN MỚI NHẤT</h2>

    <?php foreach($tinMoi as $tin){ ?>

    <div class="news-card">

        <img src="<?= $tin['anh'] ?>">

        <div class="news-content">

            <h3><?= $tin['tieude'] ?></h3>

            <p><?= $tin['mota'] ?></p>

            <span><?= $tin['time'] ?></span>

        </div>

    </div>

    <?php } ?>

</section>

<section class="section">

    <h2>TIN XEM NHIỀU</h2>

    <?php foreach($tinHot as $tin){ ?>

    <div class="news-card">

        <img src="<?= $tin['anh'] ?>">

        <div class="news-content">

            <h3><?= $tin['tieude'] ?></h3>

            <p><?= $tin['mota'] ?></p>

            <span><?= $tin['time'] ?></span>

        </div>

    </div>

    <?php } ?>

</section>

<script>

function timKiem(){

    let tuKhoa=document.getElementById("search").value.toLowerCase();

    let danhSachTin=document.querySelectorAll(".news-card");

    danhSachTin.forEach(function(tin){
        tin.style.backgroundColor="white";
    });

    if(tuKhoa=="") return;

    for(let i=0;i<danhSachTin.length;i++){

        let noiDung=danhSachTin[i].innerText.toLowerCase();

        if(noiDung.includes(tuKhoa)){

            danhSachTin[i].scrollIntoView({
                behavior:"smooth",
                block:"center"
            });

            danhSachTin[i].style.backgroundColor="#fff7b2";

            break;
        }
    }

}

</script>


<footer class="footer">

    <div class="footer-content">

        <h3>TIN TỨC 24H</h3>

        <p>
            Website cập nhật tin tức mới nhất về thời sự,
            công nghệ, giải trí và thể thao.
        </p>

        <p>
            © 2026 Tin Tức 24H. All Rights Reserved.
        </p>

    </div>

</footer>

</body>
</html>
