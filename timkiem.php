<?php
session_start();
require_once 'config/ketnoi.php';
require_once 'includes/functions.php';

$keyword = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$posts = [];
$total = 0;
$totalPages = 0;

if ($keyword !== '') {
    $search = '%' . $keyword . '%';

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM posts
        WHERE status = 'published'
        AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
    ");
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $totalPages = ceil($total / $perPage);

    $stmt = $conn->prepare("
        SELECT p.*, c.name AS ten_danhmuc
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
        AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sssii", $search, $search, $search, $perPage, $offset);
    $stmt->execute();
    $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tìm kiếm - TinNhanh</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="site-header-inner">
    <a href="index.php" class="site-logo">Tin<span>Nhanh</span></a>

    <form action="timkiem.php" method="GET" class="search-form">
      <input type="text" name="q" placeholder="Tìm kiếm bài viết..."
             value="<?= clean($keyword) ?>">
      <button type="submit">Tìm</button>
    </form>
  </div>
</header>

<main class="article-wrap" style="display:block">

  <h1 class="article-title">Tìm kiếm bài viết</h1>

  <form action="timkiem.php" method="GET" class="search-page-form">
    <input type="text" name="q" placeholder="Nhập từ khóa..."
           value="<?= clean($keyword) ?>">
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
  </form>

  <?php if ($keyword !== ''): ?>
    <p style="margin:16px 0;color:var(--text-muted)">
      Tìm thấy <strong><?= $total ?></strong> kết quả cho từ khóa:
      <strong><?= clean($keyword) ?></strong>
    </p>

    <?php if (!empty($posts)): ?>
      <div class="card-grid-search">
        <?php foreach ($posts as $post): ?>
          <article class="article-card">
            <div class="card-thumb">
              <?php if (!empty($post['thumbnail'])): ?>
                <img src="uploads/posts/<?= clean($post['thumbnail']) ?>"
                     alt="<?= clean($post['title']) ?>">
              <?php else: ?>
                <div class="article-img-placeholder">Không có ảnh</div>
              <?php endif; ?>
            </div>

            <div class="card-body">
              <span class="article-category-badge">
                <?= clean($post['ten_danhmuc'] ?? 'Tin tức') ?>
              </span>

              <h2>
                <a href="baiviet.php?id=<?= $post['id'] ?>">
                  <?= clean($post['title']) ?>
                </a>
              </h2>

              <p><?= clean($post['excerpt']) ?></p>

              <small>
                <?= formatDate($post['created_at']) ?> |
                <?= formatViews($post['views']) ?> lượt xem
              </small>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="timkiem.php?q=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>"
             class="btn btn-outline">Trang trước</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="timkiem.php?q=<?= urlencode($keyword) ?>&page=<?= $i ?>"
             class="btn <?= $i == $page ? 'btn-primary' : 'btn-ghost' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="timkiem.php?q=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>"
             class="btn btn-outline">Trang sau</a>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <p>Không tìm thấy bài viết nào phù hợp.</p>
    <?php endif; ?>

  <?php else: ?>
    <p>Nhập từ khóa để tìm kiếm bài viết.</p>
  <?php endif; ?>

</main>

</body>
</html>
