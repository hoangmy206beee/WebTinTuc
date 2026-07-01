<?php

session_start();
require_once 'config/ketnoi.php';
require_once 'includes/functions.php';


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}


$stmt = $conn->prepare("
    SELECT p.*, c.name AS ten_danhmuc
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status = 'published'
");
$stmt->bind_param('i', $id);
$stmt->execute();
$bv = $stmt->get_result()->fetch_assoc();
$stmt->close();


if (!$bv) {
    header('Location: index.php');
    exit;
}


tangLuotXem($conn, $id);


$lienquan = [];
if (!empty($bv['category_id'])) {
    $cat_id = (int)$bv['category_id'];
    $stmt2  = $conn->prepare("
        SELECT id, title, thumbnail, created_at
        FROM posts
        WHERE category_id = ? AND id != ? AND status = 'published'
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $stmt2->bind_param('ii', $cat_id, $id);
    $stmt2->execute();
    $lienquan = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();
}
// Lưu ý: KHÔNG đóng $conn ở đây vì binhluan.php (include bên dưới) còn cần dùng
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= clean($bv['title']) ?> – TinNhanh</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>


<header class="site-header">
  <div class="site-header-inner">
    <a href="index.php" class="site-logo">Tin<span>Nhanh</span></a>
    <nav class="site-nav">
      <a href="index.php">Trang chủ</a>
      <a href="danhmuc.php?slug=the-thao">Thể thao</a>
      <a href="danhmuc.php?slug=cong-nghe">Công nghệ</a>
      <a href="danhmuc.php?slug=kinh-te">Kinh tế</a>
      <a href="danhmuc.php?slug=giai-tri">Giải trí</a>
      <a href="timkiem.php">🔍 Tìm kiếm</a>
    </nav>
  </div>
</header>


<div class="article-wrap">
  <main class="article-main">

    <?php if (!empty($bv['ten_danhmuc'])): ?>
      <a href="danhmuc.php?id=<?= $bv['category_id'] ?>">
        <span class="article-category-badge"><?= clean($bv['ten_danhmuc']) ?></span>
      </a>
    <?php endif; ?>

    <h1 class="article-title"><?= clean($bv['title']) ?></h1>

    <div class="article-meta">
      <span><i class="ti ti-calendar"></i><?= formatDate($bv['created_at']) ?></span>
      <span><i class="ti ti-user"></i><?= clean($bv['author']) ?></span>
      <span><i class="ti ti-eye"></i><?= formatViews($bv['views'] + 1) ?> lượt xem</span>
    </div>


    <div class="article-img">
      <?php if (!empty($bv['thumbnail'])): ?>
        <img src="uploads/posts/<?= clean($bv['thumbnail']) ?>"
             alt="<?= clean($bv['title']) ?>"/>
      <?php else: ?>
        <div class="article-img-placeholder">
          <i class="ti ti-photo"></i>
          <span>Hình ảnh bài viết</span>
        </div>
      <?php endif; ?>
    </div>


    <div class="article-body">
      <?= $bv['content'] ?>
    </div>
    <?php include 'binhluan.php';?> 
  </main>


  <aside class="article-sidebar">

    <div class="sidebar-box">
      <div class="sidebar-title"><i class="ti ti-eye"></i>Lượt xem</div>
      <div class="view-counter">
        <div>
          <div class="view-num"><?= formatViews($bv['views'] + 1) ?></div>
          <div class="view-label">lượt xem bài viết này</div>
        </div>
        <i class="ti ti-trending-up" style="font-size:32px;color:var(--navy)"></i>
      </div>
    </div>

    <?php if (!empty($lienquan)): ?>
    <div class="sidebar-box">
      <div class="sidebar-title"><i class="ti ti-news"></i>Bài viết liên quan</div>
      <?php foreach ($lienquan as $lq): ?>
        <a href="baiviet.php?id=<?= $lq['id'] ?>" class="related-item" style="text-decoration:none">
          <div class="related-thumb">
            <?php if (!empty($lq['thumbnail'])): ?>
              <img src="uploads/posts/<?= clean($lq['thumbnail']) ?>"
                   alt="<?= clean($lq['title']) ?>"/>
            <?php else: ?>
              <i class="ti ti-photo"></i>
            <?php endif; ?>
          </div>
          <div class="related-text">
            <?= clean($lq['title']) ?>
            <small><?= formatDate($lq['created_at']) ?></small>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </aside>
</div>

</body>
</html>
