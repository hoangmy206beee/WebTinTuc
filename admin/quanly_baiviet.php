<?php
session_start();
require_once '../config/ketnoi.php';
require_once '../includes/functions.php';

requireAdmin();

$action  = $_GET['action'] ?? 'list';
$message = '';
$msgType = 'success';

if ($action === 'xoa' && isset($_GET['id'])) {
    $xoa_id = (int)$_GET['id'];

    $res = $conn->query("SELECT thumbnail FROM posts WHERE id = $xoa_id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['thumbnail'])) {
            $duongdan = '../uploads/posts/' . $row['thumbnail'];
            if (file_exists($duongdan)) unlink($duongdan);
        }
    }

    $conn->query("DELETE FROM posts WHERE id = $xoa_id");
    $message = '✅ Đã xóa bài viết thành công.';
    $action  = 'list';
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id    = (int)($_POST['post_id']     ?? 0);
    $title      = trim($_POST['title']        ?? '');
    $excerpt    = trim($_POST['excerpt']      ?? '');
    $content    = $_POST['content']           ?? '';
    $category_id= (int)($_POST['category_id'] ?? 0) ?: null;
    $author     = trim($_POST['author']       ?? 'Admin');
    $status     = $_POST['status']            ?? 'draft';

    if (empty($title))   $errors[] = 'Tiêu đề không được để trống.';
    if (empty($content)) $errors[] = 'Nội dung không được để trống.';

    $thumbnail = $_POST['thumbnail_cu'] ?? ''; 

    if (!empty($_FILES['thumbnail']['name'])) {
        $file    = $_FILES['thumbnail'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Ảnh chỉ chấp nhận: JPG, PNG, WebP.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Ảnh vượt quá 5MB.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Lỗi khi tải ảnh lên.';
        } else {
            $filename   = time() . '_' . uniqid() . '.' . $ext;
            $uploadPath = '../uploads/posts/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                if (!empty($_POST['thumbnail_cu'])) {
                    $old = '../uploads/posts/' . $_POST['thumbnail_cu'];
                    if (file_exists($old)) unlink($old);
                }
                $thumbnail = $filename;
            } else {
                $errors[] = 'Không lưu được ảnh. Kiểm tra quyền thư mục uploads/posts/';
            }
        }
    }

    if (empty($errors)) {
        if ($post_id > 0) {
            $stmt = $conn->prepare("
                UPDATE posts
                SET title=?, excerpt=?, content=?, thumbnail=?,
                    category_id=?, author=?, status=?
                WHERE id=?
            ");
            $stmt->bind_param('ssssissi',
                $title, $excerpt, $content, $thumbnail,
                $category_id, $author, $status, $post_id
            );
        } else {
            $stmt = $conn->prepare("
                INSERT INTO posts (title, excerpt, content, thumbnail, category_id, author, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ssssis s',
                $title, $excerpt, $content, $thumbnail,
                $category_id, $author, $status
            );
   
            $stmt = $conn->prepare("
                INSERT INTO posts (title, excerpt, content, thumbnail, category_id, author, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ssssiss',
                $title, $excerpt, $content, $thumbnail,
                $category_id, $author, $status
            );
        }

        if ($stmt->execute()) {
            $stmt->close();
            $loai    = $post_id > 0 ? 'capnhat' : 'them';
            $message = $loai === 'them'
                ? '✅ Đã đăng bài viết thành công!'
                : '✅ Đã cập nhật bài viết thành công!';
            $msgType = 'success';
            $action  = 'list';
        } else {
            $errors[] = 'Lỗi database: ' . $conn->error;
        }
    }

    // Nếu có lỗi → quay lại form
    if (!empty($errors)) {
        $action = $post_id > 0 ? 'sua' : 'them';
    }
}


$bv_edit = null;
if ($action === 'sua' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $stmt    = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $bv_edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$bv_edit) { $action = 'list'; }
}


if (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bv_edit = [
        'id'          => (int)($_POST['post_id']     ?? 0),
        'title'       => $_POST['title']              ?? '',
        'excerpt'     => $_POST['excerpt']            ?? '',
        'content'     => $_POST['content']            ?? '',
        'thumbnail'   => $_POST['thumbnail_cu']       ?? '',
        'category_id' => $_POST['category_id']        ?? '',
        'author'      => $_POST['author']             ?? '',
        'status'      => $_POST['status']             ?? 'draft',
    ];
}

$danh_sach = [];
$tong      = 0;
$stats     = [];
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($action === 'list') {

    $where  = ['1=1'];
    $params = [];
    $types  = '';

    $keyword = trim($_GET['keyword'] ?? '');
    if ($keyword !== '') {
        $where[]  = 'p.title LIKE ?';
        $params[] = '%' . $keyword . '%';
        $types   .= 's';
    }

    $filter_cat = (int)($_GET['category'] ?? 0);
    if ($filter_cat > 0) {
        $where[]  = 'p.category_id = ?';
        $params[] = $filter_cat;
        $types   .= 'i';
    }

    $filter_status = $_GET['status'] ?? '';
    if (in_array($filter_status, ['published','draft','pending'])) {
        $where[]  = 'p.status = ?';
        $params[] = $filter_status;
        $types   .= 's';
    }

    $whereStr = implode(' AND ', $where);


    $perPage  = 8;
    $trang    = max(1, (int)($_GET['trang'] ?? 1));
    $offset   = ($trang - 1) * $perPage;

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM posts p WHERE $whereStr");
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $tong      = $stmt->get_result()->fetch_assoc()['total'];
    $tongTrang = ceil($tong / $perPage);
    $stmt->close();

    $params[] = $perPage;
    $params[] = $offset;
    $types   .= 'ii';
    $stmt = $conn->prepare("
        SELECT p.id, p.title, p.thumbnail, p.status, p.views, p.created_at, p.updated_at,
               c.name AS ten_danhmuc
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $whereStr
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $danh_sach = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stats = $conn->query("
        SELECT
            COUNT(*) AS tong,
            SUM(status='published') AS da_dang,
            SUM(status='draft')     AS nhap,
            SUM(status='pending')   AS cho_duyet,
            COALESCE(SUM(views),0)  AS tong_views
        FROM posts
    ")->fetch_assoc();
}

$conn->close();

function pageUrl($trang) {
    $q = $_GET;
    $q['trang'] = $trang;
    unset($q['action'], $q['xoa']);
    return '?action=list&' . http_build_query($q);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Quản lý bài viết – TinNhanh Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../style.css"/>
</head>
<body>

<div class="admin-layout">

  <nav class="admin-sidebar">
    <div class="admin-sidebar-header">
      <h3>TinNhanh Admin</h3>
      <p>Xin chào, <?= clean($_SESSION['username'] ?? 'Admin') ?></p>
    </div>

    <div class="admin-nav-section">Quản lý</div>
    <a href="index.php" class="admin-nav-item">
      <i class="ti ti-layout-dashboard"></i>Dashboard
    </a>
    <a href="quanly_baiviet.php" class="admin-nav-item <?= in_array($action,['list','them','sua']) ? 'active':'' ?>">
      <i class="ti ti-news"></i>Bài viết
    </a>
    <a href="quanly_binhluan.php" class="admin-nav-item">
      <i class="ti ti-message-circle"></i>Bình luận
    </a>
    <a href="quanly_danhmuc.php" class="admin-nav-item">
      <i class="ti ti-category"></i>Danh mục
    </a>
    <a href="quanly_taikhoan.php" class="admin-nav-item">
      <i class="ti ti-users"></i>Tài khoản
    </a>

    <div class="admin-nav-section">Tài khoản</div>
    <a href="../dangnhap.php?logout=1" class="admin-nav-item">
      <i class="ti ti-logout"></i>Đăng xuất
    </a>
  </nav>

  <main class="admin-main">

    <?php if ($action === 'list'): ?>

    <div class="admin-topbar">
      <h2><i class="ti ti-news"></i> Quản lý bài viết</h2>
      <a href="?action=them" class="btn btn-primary">
        <i class="ti ti-plus"></i>Viết bài mới
      </a>
    </div>

    <div class="admin-content">

      <?php if ($message): flashMessage($message, $msgType); endif; ?>

      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-label">Tổng bài viết</div>
          <div class="stat-value"><?= $stats['tong'] ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Đã xuất bản</div>
          <div class="stat-value"><?= $stats['da_dang'] ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Chờ duyệt</div>
          <div class="stat-value warning"><?= $stats['cho_duyet'] ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Tổng lượt xem</div>
          <div class="stat-value"><?= formatViews($stats['tong_views']) ?></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3><i class="ti ti-list"></i> Danh sách bài viết</h3>
          <span class="card-meta"><?= $tong ?> bài viết</span>
        </div>
        <div class="card-body">

          <form method="GET" action="quanly_baiviet.php">
            <input type="hidden" name="action" value="list"/>
            <div class="filters">
              <input type="text" name="keyword" class="search"
                     placeholder="🔍  Tìm kiếm tiêu đề..."
                     value="<?= clean($_GET['keyword'] ?? '') ?>"/>
              <select name="category" class="filter-category">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"
                    <?= ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                    <?= clean($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <select name="status" class="filter-status">
                <option value="">Tất cả trạng thái</option>
                <option value="published" <?= ($_GET['status']??'')==='published'?'selected':'' ?>>Đã xuất bản</option>
                <option value="draft"     <?= ($_GET['status']??'')==='draft'    ?'selected':'' ?>>Nháp</option>
                <option value="pending"   <?= ($_GET['status']??'')==='pending'  ?'selected':'' ?>>Chờ duyệt</option>
              </select>
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="ti ti-search"></i>Lọc
              </button>
              <a href="?action=list" class="btn btn-ghost btn-sm">
                <i class="ti ti-x"></i>Xóa lọc
              </a>
            </div>
          </form>

          <table class="data-table">
            <thead>
              <tr>
                <th style="width:36px"></th>
                <th>Tiêu đề bài viết</th>
                <th style="width:100px">Danh mục</th>
                <th style="width:80px">Lượt xem</th>
                <th style="width:100px">Ngày đăng</th>
                <th style="width:100px">Trạng thái</th>
                <th style="width:130px">Thao tác</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($danh_sach)): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">
                  <i class="ti ti-mood-empty" style="font-size:32px;display:block;margin-bottom:8px"></i>
                  Không tìm thấy bài viết nào.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($danh_sach as $bv):
                $badgeMap = [
                  'published' => ['badge-published','Xuất bản'],
                  'draft'     => ['badge-draft',    'Nháp'],
                  'pending'   => ['badge-pending',  'Chờ duyệt'],
                ];
                [$cls, $nhan] = $badgeMap[$bv['status']] ?? ['badge-draft', $bv['status']];
              ?>
              <tr>
                <td>
                  <div class="thumb-mini">
                    <?php if (!empty($bv['thumbnail'])): ?>
                      <img src="../uploads/posts/<?= clean($bv['thumbnail']) ?>"
                           alt="<?= clean($bv['title']) ?>"/>
                    <?php else: ?>
                      <i class="ti ti-photo"></i>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <div class="post-title-cell">
                    <?= clean($bv['title']) ?>
                    <small>Cập nhật: <?= formatDate($bv['updated_at'], 'd/m/Y H:i') ?></small>
                  </div>
                </td>
                <td><?= clean($bv['ten_danhmuc'] ?? '—') ?></td>
                <td><?= formatViews($bv['views']) ?></td>
                <td><?= formatDate($bv['created_at']) ?></td>
                <td><span class="badge <?= $cls ?>"><?= $nhan ?></span></td>
                <td>
                  <div class="action-btns">
                    <a href="../baiviet.php?id=<?= $bv['id'] ?>"
                       class="btn btn-sm btn-outline" target="_blank" title="Xem">
                      <i class="ti ti-eye"></i>
                    </a>
                    <a href="?action=sua&id=<?= $bv['id'] ?>"
                       class="btn btn-sm btn-warning" title="Sửa">
                      <i class="ti ti-edit"></i>
                    </a>
                    <a href="?action=xoa&id=<?= $bv['id'] ?>"
                       class="btn btn-sm btn-danger" title="Xóa"
                       onclick="return confirm('Bạn có chắc muốn xóa bài này không?')">
                      <i class="ti ti-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>

          <?php if (!empty($tongTrang) && $tongTrang > 1): ?>
          <div style="display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap">
            <?php if ($trang > 1): ?>
              <a href="<?= pageUrl($trang - 1) ?>" class="btn btn-sm btn-outline">
                <i class="ti ti-chevron-left"></i> Trang trước
              </a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $tongTrang; $i++): ?>
              <a href="<?= pageUrl($i) ?>"
                 class="btn btn-sm <?= $i == $trang ? 'btn-primary' : 'btn-ghost' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>
            <?php if ($trang < $tongTrang): ?>
              <a href="<?= pageUrl($trang + 1) ?>" class="btn btn-sm btn-outline">
                Trang sau <i class="ti ti-chevron-right"></i>
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>

        </div>
      </div>

    </div>


    <?php else: ?>

    <div class="admin-topbar">
      <h2>
        <i class="ti ti-<?= $action==='sua' ? 'edit' : 'pencil-plus' ?>"></i>
        <?= $action === 'sua' ? 'Sửa bài viết' : 'Viết bài mới' ?>
      </h2>
      <a href="?action=list" class="btn btn-ghost btn-sm">
        <i class="ti ti-arrow-left"></i>Quay lại danh sách
      </a>
    </div>

    <div class="admin-content">

      <?php if (!empty($errors)): ?>
        <div style="background:#f8d7da;color:#842029;border:1px solid #f5c2c7;
                    padding:12px 16px;border-radius:6px;margin-bottom:16px;font-size:14px">
          <strong>⚠️ Vui lòng kiểm tra lại:</strong>
          <ul style="margin:8px 0 0 20px">
            <?php foreach ($errors as $e): ?>
              <li><?= clean($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="quanly_baiviet.php" enctype="multipart/form-data">
        <input type="hidden" name="post_id"
               value="<?= (int)($bv_edit['id'] ?? 0) ?>"/>
        <input type="hidden" name="thumbnail_cu"
               value="<?= clean($bv_edit['thumbnail'] ?? '') ?>"/>

        <div class="write-layout">

    
          <div>
            <div class="card">
              <div class="card-header">
                <h3><i class="ti ti-file-text"></i> Nội dung bài viết</h3>
              </div>
              <div class="card-body">

                <div class="form-group">
                  <label for="title">Tiêu đề <span class="required">*</span></label>
                  <input type="text" id="title" name="title" class="title-input"
                         placeholder="Nhập tiêu đề bài viết..."
                         value="<?= clean($bv_edit['title'] ?? '') ?>" required/>
                </div>

                <div class="form-group">
                  <label for="excerpt">Mô tả ngắn</label>
                  <textarea id="excerpt" name="excerpt" rows="3"
                            placeholder="Tóm tắt ngắn hiển thị trên trang chủ..."
                  ><?= clean($bv_edit['excerpt'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                  <label for="content">Nội dung <span class="required">*</span></label>
                  <div class="editor-wrap">
                    <div class="editor-toolbar">
                      <button type="button" class="btn-tool bold"
                        onclick="wrapText('content','<strong>','</strong>')">B</button>
                      <button type="button" class="btn-tool italic"
                        onclick="wrapText('content','<em>','</em>')">I</button>
                      <button type="button" class="btn-tool"
                        onclick="wrapText('content','<h2>','</h2>')">H2</button>
                      <button type="button" class="btn-tool"
                        onclick="wrapText('content','<h3>','</h3>')">H3</button>
                      <button type="button" class="btn-tool"
                        onclick="wrapText('content','<p>','</p>')">¶ p</button>
                      <button type="button" class="btn-tool"
                        onclick="wrapText('content','<ul>\n<li>','</li>\n</ul>')">
                        <i class="ti ti-list"></i></button>
                      <button type="button" class="btn-tool"
                        onclick="wrapText('content','<a href=&quot;&quot;>','</a>')">
                        <i class="ti ti-link"></i></button>
                    </div>
                    <textarea id="content" name="content" class="editor-body"
                              rows="14" required
                              placeholder="Nhập nội dung HTML..."
                    ><?= clean($bv_edit['content'] ?? '') ?></textarea>
                  </div>
                  <small style="color:var(--text-muted);font-size:12px;display:block;margin-top:4px">
                    Hỗ trợ HTML: &lt;p&gt; &lt;h2&gt; &lt;strong&gt; &lt;em&gt; &lt;ul&gt; &lt;li&gt; &lt;a&gt;
                  </small>
                </div>

              </div>
            </div>
          </div>

          <div>

    
            <div class="card">
              <div class="card-header">
                <h3><i class="ti ti-photo"></i> Ảnh đại diện</h3>
              </div>
              <div class="card-body">
                <?php if (!empty($bv_edit['thumbnail'])): ?>
                  <div style="margin-bottom:12px;text-align:center">
                    <img src="../uploads/posts/<?= clean($bv_edit['thumbnail']) ?>"
                         style="max-width:100%;border-radius:6px;border:1px solid var(--border)"
                         alt="Ảnh hiện tại"/>
                    <p style="font-size:11px;color:var(--text-muted);margin-top:4px">Ảnh hiện tại</p>
                  </div>
                <?php endif; ?>
                <label for="thumbnail" class="upload-zone" id="uploadZone">
                  <i class="ti ti-cloud-upload"></i>
                  <p><strong><?= !empty($bv_edit['thumbnail']) ? 'Nhấn để đổi ảnh' : 'Nhấn để tải ảnh lên' ?></strong></p>
                  <p class="upload-hint">JPG, PNG, WebP – Tối đa 5MB</p>
                </label>
                <input type="file" id="thumbnail" name="thumbnail"
                       accept=".jpg,.jpeg,.png,.webp"
                       style="display:none"
                       onchange="previewAnh(this)"/>
                <p class="upload-preview">Kích thước đề xuất: 1200×630 px</p>
              </div>
            </div>


            <div class="card">
              <div class="card-header">
                <h3><i class="ti ti-settings"></i> Cài đặt</h3>
              </div>
              <div class="card-body">

                <div class="form-group">
                  <label for="category_id">Danh mục</label>
                  <select id="category_id" name="category_id">
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat['id'] ?>"
                        <?= ($bv_edit['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= clean($cat['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="status">Trạng thái</label>
                  <select id="status" name="status">
                    <option value="draft"
                      <?= ($bv_edit['status']??'draft')==='draft'     ?'selected':'' ?>>Nháp</option>
                    <option value="pending"
                      <?= ($bv_edit['status']??'')==='pending'        ?'selected':'' ?>>Chờ duyệt</option>
                    <option value="published"
                      <?= ($bv_edit['status']??'')==='published'      ?'selected':'' ?>>Xuất bản</option>
                  </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                  <label for="author">Tác giả</label>
                  <input type="text" id="author" name="author"
                         value="<?= clean($bv_edit['author'] ?? ($_SESSION['username'] ?? 'Admin')) ?>"/>
                </div>

              </div>
            </div>

      
            <div class="sidebar-actions">
              <button type="submit" class="btn btn-primary">
                <i class="ti ti-send"></i>
                <?= ($bv_edit['id'] ?? 0) > 0 ? 'Lưu thay đổi' : 'Đăng bài' ?>
              </button>
              <a href="?action=list" class="btn btn-ghost">
                <i class="ti ti-arrow-left"></i>Hủy bỏ
              </a>
            </div>

          </div>
        </div>
      </form>

    </div>
    <?php endif; ?>

  </main>
</div>

<script>

function previewAnh(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('uploadZone').innerHTML =
                `<img src="${e.target.result}"
                      style="max-width:100%;border-radius:6px;max-height:160px;object-fit:cover"/>
                 <p style="margin-top:8px;font-size:12px;color:var(--text-muted)">
                     ${input.files[0].name}
                 </p>`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}


function wrapText(id, open, close) {
    const ta    = document.getElementById(id);
    const s     = ta.selectionStart;
    const e     = ta.selectionEnd;
    const sel   = ta.value.substring(s, e);
    ta.value    = ta.value.substring(0, s) + open + sel + close + ta.value.substring(e);
    ta.focus();
    ta.selectionStart = s + open.length;
    ta.selectionEnd   = s + open.length + sel.length;
}
</script>

</body>
</html>
