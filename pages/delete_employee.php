<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$ma_nv = $_GET['id'] ?? '';
if (empty($ma_nv)) {
    header("Location: employees.php");
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM nhanvien WHERE ma_nv = ?");
    $stmt->execute([$ma_nv]);
    
    $_SESSION['success'] = "Xóa nhân viên thành công";
} catch (PDOException $e) {
    $_SESSION['error'] = "Lỗi khi xóa nhân viên: " . $e->getMessage();
}

header("Location: employees.php");
exit();
?>