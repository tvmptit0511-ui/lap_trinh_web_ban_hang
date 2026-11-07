<?php
/**
 * Các hàm tiện ích cho website
 */

/**
 * Escape output để tránh XSS
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Kiểm tra CSRF token
 */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            die('CSRF token không hợp lệ.');
        }
    }
}

/**
 * Tạo CSRF input hidden
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get và xóa flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Kiểm tra đã đăng nhập
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra là admin
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Yêu cầu đăng nhập
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(BASE_URL . 'login.php');
    }
}

/**
 * Yêu cầu quyền admin
 */
function require_admin() {
    if (!is_logged_in()) {
        redirect(BASE_URL . 'login.php');
    }
    if (!is_admin()) {
        http_response_code(403);
        die('Bạn không có quyền truy cập trang này.');
    }
}

/**
 * Format giá tiền
 */
function format_price($price) {
    return number_format($price, 0, ',', '.') . 'đ';
}

/**
 * Format ngày giờ
 */
function format_datetime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Format ngày
 */
function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Tạo slug từ tiêu đề
 */
function create_slug($str) {
    $str = mb_strtolower($str, 'UTF-8');

    $replacements = [
        'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
        'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
        'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
        'đ' => 'd',
        'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
        'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
        'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
        'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
        'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
        'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
        'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
    ];

    $str = strtr($str, $replacements);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    $str = trim($str, '-');

    return $str;
}

/**
 * Upload ảnh an toàn
 */
function upload_image($file) {
    // Kiểm tra có lỗi không
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Lỗi khi tải ảnh lên.');
    }

    // Kiểm tra kích thước
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('Kích thước file quá lớn. Tối đa ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.');
    }

    // Kiểm tra extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        throw new Exception('Chỉ chấp nhận file ảnh: ' . implode(', ', ALLOWED_EXTENSIONS));
    }

    // Kiểm tra nội dung file là ảnh thật
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime, $allowed_mimes)) {
        throw new Exception('File không phải là ảnh hợp lệ.');
    }

    // Tạo tên file ngẫu nhiên
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $filepath = UPLOAD_PATH . $filename;

    // Di chuyển file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Không thể lưu file.');
    }

    return $filename;
}

/**
 * Xóa file ảnh
 */
function delete_image($filename) {
    if ($filename && file_exists(UPLOAD_PATH . $filename)) {
        unlink(UPLOAD_PATH . $filename);
    }
}

/**
 * Lấy trạng thái đơn hàng
 */
function get_order_status_label($status) {
    $labels = [
        'pending' => 'Chờ xử lý',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy'
    ];
    return $labels[$status] ?? $status;
}

/**
 * Lấy class CSS cho trạng thái đơn
 */
function get_order_status_class($status) {
    $classes = [
        'pending' => 'status-pending',
        'shipping' => 'status-shipping',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled'
    ];
    return $classes[$status] ?? '';
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Kiểm tra khóa đăng nhập (D10)
 */
function check_login_attempts() {
    $lock_file = sys_get_temp_dir() . '/login_lock_' . md5($_SERVER['REMOTE_ADDR']);

    if (file_exists($lock_file)) {
        $lock_data = json_decode(file_get_contents($lock_file), true);

        // Kiểm tra còn bị khóa không
        if ($lock_data['locked_until'] > time()) {
            $remaining = $lock_data['locked_until'] - time();
            throw new Exception("Tài khoản tạm khóa do đăng nhập sai quá nhiều. Vui lòng thử lại sau " . ceil($remaining / 60) . " phút.");
        }

        // Hết thời gian khóa, reset
        if ($lock_data['locked_until'] <= time()) {
            unlink($lock_file);
            return;
        }
    }
}

/**
 * Ghi nhận lần đăng nhập thất bại
 */
function record_failed_login() {
    $lock_file = sys_get_temp_dir() . '/login_lock_' . md5($_SERVER['REMOTE_ADDR']);

    $attempts = 1;
    if (file_exists($lock_file)) {
        $lock_data = json_decode(file_get_contents($lock_file), true);
        $attempts = $lock_data['attempts'] + 1;
    }

    // Nếu đủ 5 lần, khóa 5 phút
    if ($attempts >= 5) {
        $lock_data = [
            'attempts' => $attempts,
            'locked_until' => time() + (5 * 60) // 5 phút
        ];
        file_put_contents($lock_file, json_encode($lock_data));
    } else {
        $lock_data = [
            'attempts' => $attempts,
            'locked_until' => 0
        ];
        file_put_contents($lock_file, json_encode($lock_data));
    }
}

/**
 * Reset login attempts khi đăng nhập thành công
 */
function reset_login_attempts() {
    $lock_file = sys_get_temp_dir() . '/login_lock_' . md5($_SERVER['REMOTE_ADDR']);
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }
}

/**
 * Phân trang
 */
function paginate($total, $per_page, $current_page) {
    $total_pages = ceil($total / $per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $per_page;

    return [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset
    ];
}
