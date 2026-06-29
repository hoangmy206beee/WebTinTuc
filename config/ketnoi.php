<<<<<<< HEAD
<?php
$localhost = "localhost";
$username = "root";       
$password = "";           
$dbname = "db_tintuc";    

$conn = new mysqli($localhost, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Lỗi kết nối Database: " . $conn->connect_error);
}


$conn->set_charset("utf8mb4");

?>
=======
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
>>>>>>> a4893002cebb532187147af79bab3ef1732b54e2
