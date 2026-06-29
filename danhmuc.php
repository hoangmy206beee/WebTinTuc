<?php
session_start();
require_once 'config/ketnoi.php';
require_once 'includes/functions.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    header("Location: index.php");
    exit;
}

/* Lấy thông tin danh mục */
$stmt = $conn->prepare("
    SELECT *
    FROM categories
    WHERE id = ?
");
$stmt->bind_param("i", $category_id);
$stmt->execute();

$category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$category) {
    header("Location: index.php");
    exit;
}

/* Phân trang */
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

/* Đếm tổng bài viết */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM posts
    WHERE category_id = ?
    AND status = 'published'
");
$stmt->bind_param("i", $category_id);
$stmt->execute();

$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$totalPages = ceil($total / $perPage);

/* Lấy bài viết */
$stmt = $conn->prepare("
    SELECT *
    FROM posts
    WHERE category_id = ?
    AND status = 'published'
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $category_id, $perPage, $offset);
$stmt->execute();

$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= clean($category['name']) ?> - TinNhanh</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="site-header-inner">

        <a href="index.php" class="site-logo">
            Tin<span>Nhanh</span>
        </a>

        <nav class="site-nav">
            <a href="index.php">Trang chủ</a>
            <a href="timkiem.php">Tìm kiếm</a>
        </nav>

    </div>
</header>

<main class="article-wrap" style="display:block">

    <span class="article-category-badge">
        <?= clean($category['name']) ?>
    </span>

    <h1 class="article-title">
        Danh mục: <?= clean($category['name']) ?>
    </h1>

    <p style="margin-bottom:20px;color:var(--text-muted)">
        Có <?= $total ?> bài viết trong danh mục này.
    </p>

    <?php if(!empty($posts)): ?>

        <div class="card-grid-search">

            <?php foreach($posts as $post): ?>

                <article class="article-card">

                    <div class="card-thumb">

                        <?php if(!empty($post['thumbnail'])): ?>

                            <img src="uploads/posts/<?= clean($post['thumbnail']) ?>"
                                 alt="<?= clean($post['title']) ?>">

                        <?php else: ?>

                            <div class="article-img-placeholder">
                                Không có ảnh
                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="card-body">

                        <h2>
                            <a href="baiviet.php?id=<?= $post['id'] ?>">
                                <?= clean($post['title']) ?>
                            </a>
                        </h2>

                        <p>
                            <?= clean($post['excerpt']) ?>
                        </p>

                        <small>
                            <?= formatDate($post['created_at']) ?>
                            |
                            <?= formatViews($post['views']) ?>
                            lượt xem
                        </small>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

        <?php if($totalPages > 1): ?>

            <div class="pagination">

                <?php if($page > 1): ?>
                    <a href="?id=<?= $category_id ?>&page=<?= $page-1 ?>"
                       class="btn btn-outline">
                        Trang trước
                    </a>
                <?php endif; ?>

                <?php for($i=1; $i<=$totalPages; $i++): ?>

                    <a href="?id=<?= $category_id ?>&page=<?= $i ?>"
                       class="btn <?= ($i==$page) ? 'btn-primary' : 'btn-ghost' ?>">
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

                <?php if($page < $totalPages): ?>
                    <a href="?id=<?= $category_id ?>&page=<?= $page+1 ?>"
                       class="btn btn-outline">
                        Trang sau
                    </a>
                <?php endif; ?>

            </div>

        <?php endif; ?>

    <?php else: ?>

        <div class="card">
            <div class="card-body">
                Chưa có bài viết nào trong danh mục này.
            </div>
        </div>

    <?php endif; ?>

</main>

</body>
</html>
