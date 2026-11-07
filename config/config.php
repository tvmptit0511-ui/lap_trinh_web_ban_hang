<?php
/**
 * Cấu hình chung cho website Cửa hàng Trần Văn Mỹ
 * MSSV: N25DTCN035
 */

// Tên website
define('SITE_NAME', 'Cửa hàng Trần Văn Mỹ');
define('SITE_SLOGAN', 'Điện thoại & phụ kiện chính hãng');

// Thông tin sinh viên
define('STUDENT_NAME', 'Trần Văn Mỹ');
define('STUDENT_ID', 'N25DTCN035');

// Đường dẫn gốc
define('BASE_URL', 'http://localhost/minishop/');
define('BASE_PATH', __DIR__ . '/../');

// Chế độ debug
define('APP_DEBUG', true);

// Cấu hình phân trang
define('ITEMS_PER_PAGE', 8);

// Cấu hình upload ảnh
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
