<?php
$host = 'localhost';
$dbname = 'ql_nhansu';
$username = 'root';
$password = 'Tinh692003@';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}
?>