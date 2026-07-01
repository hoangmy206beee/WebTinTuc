<?php
/**
 * Các hàm dùng chung cho toàn bộ site
 */

// Lọc dữ liệu trước khi in ra HTML, chống XSS
function clean($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Định dạng ngày giờ kiểu Việt Nam: dd/mm/YYYY HH:i
function formatDate($datetime) {
    if (empty($datetime)) return '';
    $time = strtotime($datetime);
    return date('d/m/Y H:i', $time);
}

// Định dạng số lượt xem cho gọn (vd: 12.500)
function formatViews($views) {
    return number_format((int)$views, 0, ',', '.');
}

// Tăng lượt xem cho 1 bài viết
function tangLuotXem($conn, $id) {
    $stmt = $conn->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}
