<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['anh'])) {
    $targetDir = __DIR__ . '/upload/';
    $fileName = time() . '_' . basename($_FILES['anh']['name']);
    $targetFile = $targetDir . $fileName;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    $error = '';

    if (!in_array($_FILES['anh']['type'], $allowedTypes, true)) {
        $error = 'Chỉ chấp nhận file ảnh JPG, PNG, GIF hoặc WEBP.';
    } elseif ($_FILES['anh']['size'] > 2 * 1024 * 1024) {
        $error = 'Kích thước ảnh tối đa là 2MB.';
    } elseif (!is_uploaded_file($_FILES['anh']['tmp_name'])) {
        $error = 'Tệp không hợp lệ.';
    } elseif (!move_uploaded_file($_FILES['anh']['tmp_name'], $targetFile)) {
        $error = 'Không thể lưu ảnh lên server.';
    }

    if ($error === '') {
        $uploadedUrl = 'upload/' . $fileName;
        $message = 'Upload ảnh thành công!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload ảnh</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px; }
        .box { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; }
        input[type="file"] { margin: 10px 0; }
        button { padding: 8px 14px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .success { color: green; margin-top: 10px; }
        .error { color: red; margin-top: 10px; }
        .link { display: block; margin-top: 10px; word-break: break-all; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Upload ảnh</h2>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="anh">Chọn ảnh:</label>
            <input type="file" name="anh" id="anh" required>
            <button type="submit">Tải lên</button>
        </form>

        <?php if (!empty($message)) : ?>
            <p class="success"><?= htmlspecialchars($message) ?></p>
            <a class="link" href="<?= htmlspecialchars($uploadedUrl) ?>" target="_blank">Xem ảnh đã upload</a>
        <?php endif; ?>

        <?php if (!empty($error)) : ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
