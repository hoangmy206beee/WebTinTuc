<?php
session_start();
require_once 'config/ketnoi.php';
require_once 'includes/functions.php';

// Danh mục để hiển thị menu
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Tin mới nhất (6 bài)
$tinMoi = $conn->query("
    SELECT id, title, excerpt, thumbnail, created_at
    FROM posts
    WHERE status = 'published'
    ORDER BY created_at DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Tin xem nhiều (3 bài)
$tinHot = $conn->query("
    SELECT id, title, excerpt, thumbnail, views
    FROM posts
    WHERE status = 'published'
    ORDER BY views DESC
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin Tức 24H</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="site-header-inner">
        <a href="index.php" class="site-logo">Tin<span>Tức24H</span></a>

        <nav class="site-nav">
            <a href="index.php" class="active">Trang chủ</a>
            <?php foreach ($categories as $cat): ?>
                <a href="danhmuc.php?id=<?= $cat['id'] ?>"><?= clean($cat['name']) ?></a>
            <?php endforeach; ?>
            <a href="timkiem.php">🔍 Tìm kiếm</a>
        </nav>
    </div>
</header>

<section class="section">
    <h2>TIN MỚI NHẤT</h2>

    <?php if (empty($tinMoi)): ?>
        <p>Chưa có bài viết nào. Hãy thêm bài viết trong trang quản trị (admin).</p>
    <?php else: ?>
        <div class="card-grid-search">
            <?php foreach ($tinMoi as $tin): ?>
                <article class="article-card">
                    <div class="card-thumb">
                        <?php if (!empty($tin['thumbnail'])): ?>
                            <img src="uploads/posts/<?= clean($tin['thumbnail']) ?>" alt="<?= clean($tin['title']) ?>">
                        <?php else: ?>
                            <div class="article-img-placeholder">Không có ảnh</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h2><a href="baiviet.php?id=<?= $tin['id'] ?>"><?= clean($tin['title']) ?></a></h2>
                        <p><?= clean($tin['excerpt']) ?></p>
                        <small><?= formatDate($tin['created_at']) ?></small>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="section">
    <h2>TIN XEM NHIỀU</h2>

    <?php if (empty($tinHot)): ?>
        <p>Chưa có dữ liệu.</p>
    <?php else: ?>
        <div class="card-grid-search">
            <?php foreach ($tinHot as $tin): ?>
                <article class="article-card">
                    <div class="card-thumb">
                        <?php if (!empty($tin['thumbnail'])): ?>
                            <img src="uploads/posts/<?= clean($tin['thumbnail']) ?>" alt="<?= clean($tin['title']) ?>">
                        <?php else: ?>
                            <div class="article-img-placeholder">Không có ảnh</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h2><a href="baiviet.php?id=<?= $tin['id'] ?>"><?= clean($tin['title']) ?></a></h2>
                        <p><?= clean($tin['excerpt']) ?></p>
                        <small><?= formatViews($tin['views']) ?> lượt xem</small>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<footer class="footer">
    <div class="footer-content">
        <h3>TIN TỨC 24H</h3>
        <p>Website cập nhật tin tức mới nhất về thời sự, công nghệ, giải trí và thể thao.</p>
        <p>© 2026 Tin Tức 24H. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>
