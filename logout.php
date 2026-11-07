<?php
/**
 * Đăng xuất
 * Yêu cầu: C2
 */

require_once __DIR__ . '/includes/init.php';

// Xóa session
$_SESSION = [];

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// Chuyển về trang chủ
redirect(BASE_URL . '?msg=logged_out');
