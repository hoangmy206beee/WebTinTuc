```php
<?php
session_start();
require_once '../config/ketnoi.php';
require_once '../includes/functions.php';

/* =========================
   XÓA DANH MỤC
========================= */
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM categories
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: quanly_danhmuc.php");
    exit;
}

/* =========================
   LẤY DỮ LIỆU SỬA
========================= */
$editMode = false;
$editData = null;

if (isset($_GET['edit'])) {

    $editMode = true;

    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare("
        SELECT *
        FROM categories
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

/* =========================
   THÊM DANH MỤC
========================= */
if (isset($_POST['them'])) {

    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);

    if ($name != '') {

        $stmt = $conn->prepare("
            INSERT INTO categories(name, slug)
            VALUES (?, ?)
        ");

        $stmt->bind_param("ss", $name, $slug);
        $stmt->execute();

        header("Location: quanly_danhmuc.php");
        exit;
    }
}

/* =========================
   CẬP NHẬT
========================= */
if (isset($_POST['capnhat'])) {

    $id = (int)$_POST['id'];

    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);

    $stmt = $conn->prepare("
        UPDATE categories
        SET name = ?, slug = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssi",
        $name,
        $slug,
        $id
    );

    $stmt->execute();

    header("Location: quanly_danhmuc.php");
    exit;
}

/* =========================
   DANH SÁCH DANH MỤC
========================= */
$result = $conn->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Quản lý danh mục</title>

<link rel="stylesheet" href="../style.css">

<style>

.admin-container{
    max-width:1100px;
    margin:30px auto;
    padding:20px;
}

.form-card,
.table-card{
    background:#fff;
    border-radius:10px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
}

.form-group input{
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:6px;
}

.data-table{
    width:100%;
    border-collapse:collapse;
}

.data-table th,
.data-table td{
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:left;
}

.data-table th{
    background:#f5f5f5;
}

.action-btns{
    display:flex;
    gap:8px;
}

.page-title{
    margin-bottom:20px;
}

</style>

</head>

<body>

<div class="admin-container">

    <h1 class="page-title">
        Quản lý danh mục
    </h1>

    <div class="form-card">

        <h3>
            <?= $editMode ? 'Cập nhật danh mục' : 'Thêm danh mục mới' ?>
        </h3>

        <br>

        <form method="POST">

            <?php if($editMode): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= $editData['id'] ?>"
                >

            <?php endif; ?>

            <div class="form-group">
                <label>Tên danh mục</label>

                <input
                    type="text"
                    name="name"
                    required
                    value="<?= $editMode ? clean($editData['name']) : '' ?>"
                >
            </div>

            <div class="form-group">
                <label>Slug</label>

                <input
                    type="text"
                    name="slug"
                    value="<?= $editMode ? clean($editData['slug']) : '' ?>"
                >
            </div>

            <?php if($editMode): ?>

                <button
                    type="submit"
                    name="capnhat"
                    class="btn btn-warning"
                >
                    Cập nhật
                </button>

                <a
                    href="quanly_danhmuc.php"
                    class="btn btn-outline"
                >
                    Hủy
                </a>

            <?php else: ?>

                <button
                    type="submit"
                    name="them"
                    class="btn btn-primary"
                >
                    Thêm danh mục
                </button>

            <?php endif; ?>

        </form>

    </div>

    <div class="table-card">

        <h3>Danh sách danh mục</h3>

        <br>

        <table class="data-table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Tên danh mục</th>
                    <th>Slug</th>
                    <th>Thao tác</th>
                </tr>

            </thead>

            <tbody>

            <?php while($row = $result->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?= $row['id'] ?>
                    </td>

                    <td>
                        <?= clean($row['name']) ?>
                    </td>

                    <td>
                        <?= clean($row['slug']) ?>
                    </td>

                    <td>

                        <div class="action-btns">

                            <a
                                href="?edit=<?= $row['id'] ?>"
                                class="btn btn-warning btn-sm"
                            >
                                Sửa
                            </a>

                            <a
                                href="?delete=<?= $row['id'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc muốn xóa?')"
                            >
                                Xóa
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
```
