<?php
// =====================================================
// config/ketnoi.php — Kết nối MySQL
// Thay đổi thông tin cho phù hợp máy chủ của nhóm
// =====================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // username MySQL
define('DB_PASS', '');          // password MySQL
define('DB_NAME', 'tinnhanh');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('<p style="color:red;font-family:Calibri,sans-serif;padding:20px">
        ❌ Không thể kết nối database: ' . $conn->connect_error . '
    </p>');
}
?>
