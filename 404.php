<?php
/**
 * Trang 404 - Không tìm thấy
 * Yêu cầu: B5, D5
 */

require_once __DIR__ . '/includes/init.php';

$current_page = '404';
$page_title = 'Không tìm thấy trang - ' . SITE_NAME;

http_response_code(404);

require_once __DIR__ . '/includes/header.php';
?>

<div style="text-align: center; padding: 4rem 0;">
    <h1 style="font-size: 6rem; font-weight: 700; color: var(--text-light); margin: 0;">404</h1>
    <h2 style="font-size: 2rem; margin: 1rem 0;">Không tìm thấy trang</h2>
    <p style="color: var(--text-light); margin: 2rem 0;">
        Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.
    </p>
    <a href="<?= BASE_URL ?>" class="btn btn-primary">Về trang chủ</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
