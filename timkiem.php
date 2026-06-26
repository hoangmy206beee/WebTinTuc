<?php
require_once __DIR__ . '/../includes/functions.php';
$q = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$pageTitle = $q ? 'Tìm kiếm: ' . $q : 'Tìm kiếm';

$articles = [];
$total = 0;
if ($q) {
    $total = countArticles(['search' => $q]);
    $articles = getArticles(['search' => $q], $perPage, ($page - 1) * $perPage);
}

require_once 'includes/header.php';
?>

<div class="search-hero">
  <div class="container">
    <h2 style="text-align:center;font-size:22px;font-weight:800;margin-bottom:16px;color:var(--secondary)"><i class="fas fa-search"></i> Tìm kiếm tin tức</h2>
    <form class="search-big" action="" method="GET">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Nhập từ khóa tìm kiếm..." autofocus>
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <?php if ($q): ?>
      <p style="text-align:center;margin-top:12px;font-size:14px;color:var(--text-mid)">
        Tìm thấy <strong><?= $total ?></strong> kết quả cho "<strong><?= e($q) ?></strong>"
      </p>
    <?php endif; ?>
  </div>
</div>

<div class="container layout-full">
  <?php if ($q && !empty($articles)): ?>
    <div class="section-header" style="margin-top:24px">
      <h2 class="section-title">Kết quả tìm kiếm</h2>
    </div>
    <div class="card-grid" style="margin-bottom:24px">
      <?php foreach ($articles as $art):
        $img = $art['thumbnail'] ? UPLOAD_URL . $art['thumbnail'] : 'https://via.placeholder.com/400x225?text=No+Image';
      ?>
      <article class="article-card">
        <a href="<?= SITE_URL ?>/public/article.php?slug=<?= e($art['slug']) ?>" class="card-thumbnail">
          <img src="<?= e($img) ?>" alt="<?= e($art['title']) ?>" loading="lazy">
          <span class="cat-badge" style="background:<?= e($art['category_color']) ?>"><?= e($art['category_name']) ?></span>
        </a>
        <div class="card-body">
          <a href="<?= SITE_URL ?>/public/article.php?slug=<?= e($art['slug']) ?>">
            <h3 class="card-title"><?= e($art['title']) ?></h3>
          </a>
          <p class="card-excerpt"><?= e($art['excerpt']) ?></p>
          <div class="card-meta">
            <span><i class="fas fa-clock"></i> <?= timeAgo($art['published_at']) ?></span>
            <span class="card-views"><i class="fas fa-eye"></i> <?= number_format($art['views']) ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?= paginate($total, $perPage, $page, SITE_URL . '/public/search.php?q=' . urlencode($q)) ?>

  <?php elseif ($q): ?>
    <div class="empty-state" style="margin-top:24px">
      <i class="fas fa-search"></i>
      <h3>Không tìm thấy kết quả</h3>
      <p>Không có bài viết nào phù hợp với "<strong><?= e($q) ?></strong>". Thử từ khóa khác.</p>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
