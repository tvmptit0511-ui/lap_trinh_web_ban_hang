<?php
/**
 * Cấu hình kết nối cơ sở dữ liệu
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'minishop');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
    } else {
        die("Không thể kết nối đến cơ sở dữ liệu.");
    }
}
