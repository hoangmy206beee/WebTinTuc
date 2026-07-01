<?php
/**
 * Kết nối Database - chỉ MỘT kết nối duy nhất
 * Tên database phải khớp với file tinnhanh.sql
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tinnhanh');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<p style="color:red;font-family:Calibri,sans-serif;padding:20px">
        ❌ Không thể kết nối database: ' . $conn->connect_error . '
        <br>Kiểm tra: 1) XAMPP/MySQL đã bật chưa? 2) Database "tinnhanh" đã import chưa?
    </p>');
}

$conn->set_charset('utf8mb4');
