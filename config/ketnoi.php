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