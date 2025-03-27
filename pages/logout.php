<?php
// pages/logout.php
require_once '../includes/auth.php';

// Xóa tất cả session
$_SESSION = array();

// Nếu muốn xóa cả cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Chuyển hướng về trang login
header("Location: login.php");
exit();
?>