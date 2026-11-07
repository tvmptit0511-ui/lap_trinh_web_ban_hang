<?php
/**
 * Trang tổng quan quản trị
 * Yêu cầu: D1, D9
 */

require_once __DIR__ . '/../includes/init.php';

// Yêu cầu quyền admin (D1)
require_admin();

$current_page = 'admin-dashboard';
$page_title = 'Tổng quan - Quản trị - ' . SITE_NAME;

// D9: Số đơn theo trạng thái
$stmt = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'shipping' THEN 1 ELSE 0 END) as shipping,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders
");
$order_stats = $stmt->fetch();

// D9: Doanh thu đơn hoàn tất
$stmt = $pdo->query("
    SELECT COALESCE(SUM(total_amount), 0) as revenue
    FROM orders
    WHERE status = 'completed'
");
$revenue = $stmt->fetchColumn();

// D9: 5 sản phẩm sắp hết hàng
$stmt = $pdo->query("
    SELECT * FROM products
    ORDER BY stock ASC
    LIMIT 5
");
$low_stock_products = $stmt->fetchAll();

// Thống kê tổng quan
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Navigation quản trị -->
<nav class="admin-nav">
    <div class="container">
        <ul>
            <li><a href="<?= BASE_URL ?>admin/" class="active">Tổng quan</a></li>
            <li><a href="<?= BASE_URL ?>admin/products.php">Sản phẩm</a></li>
            <li><a href="<?= BASE_URL ?>admin/categories.php">Danh mục</a></li>
            <li><a href="<?= BASE_URL ?>admin/orders.php">Đơn hàng</a></li>
            <li><a href="<?= BASE_URL ?>">Về trang chủ</a></li>
        </ul>
    </div>
</nav>

<h1 class="page-title">Tổng quan hệ thống</h1>

<!-- Thống kê tổng quan -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
    <div style="background: var(--bg-gray); padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
            <?= $order_stats['total'] ?>
        </div>
        <div style="color: var(--text-light); margin-top: 0.5rem;">Tổng đơn hàng</div>
    </div>

    <div style="background: var(--bg-gray); padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--success-color);">
            <?= format_price($revenue) ?>
        </div>
        <div style="color: var(--text-light); margin-top: 0.5rem;">Doanh thu (hoàn tất)</div>
    </div>

    <div style="background: var(--bg-gray); padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
            <?= $total_products ?>
        </div>
        <div style="color: var(--text-light); margin-top: 0.5rem;">Sản phẩm</div>
    </div>

    <div style="background: var(--bg-gray); padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
            <?= $total_users ?>
        </div>
        <div style="color: var(--text-light); margin-top: 0.5rem;">Người dùng</div>
    </div>
</div>

<!-- D9: Số đơn theo trạng thái -->
<h2 style="margin-bottom: 1rem;">Đơn hàng theo trạng thái</h2>
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
    <div style="background: #fef3c7; padding: 1rem; border-radius: 0.5rem;">
        <div style="font-size: 1.5rem; font-weight: 700;"><?= $order_stats['pending'] ?></div>
        <div style="color: #92400e;">Chờ xử lý</div>
    </div>

    <div style="background: #dbeafe; padding: 1rem; border-radius: 0.5rem;">
        <div style="font-size: 1.5rem; font-weight: 700;"><?= $order_stats['shipping'] ?></div>
        <div style="color: #1e40af;">Đang giao</div>
    </div>

    <div style="background: #d1fae5; padding: 1rem; border-radius: 0.5rem;">
        <div style="font-size: 1.5rem; font-weight: 700;"><?= $order_stats['completed'] ?></div>
        <div style="color: #065f46;">Hoàn tất</div>
    </div>

    <div style="background: #f3f4f6; padding: 1rem; border-radius: 0.5rem;">
        <div style="font-size: 1.5rem; font-weight: 700;"><?= $order_stats['cancelled'] ?></div>
        <div style="color: #6b7280;">Đã hủy</div>
    </div>
</div>

<!-- D9: 5 sản phẩm sắp hết hàng -->
<h2 style="margin-bottom: 1rem;">Sản phẩm sắp hết hàng (tồn kho thấp nhất)</h2>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th style="width: 150px;">Giá</th>
            <th style="width: 120px;">Tồn kho</th>
            <th style="width: 150px;">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($low_stock_products as $product): ?>
            <tr>
                <td><?= $product['id'] ?></td>
                <td><?= e($product['name']) ?></td>
                <td><?= format_price($product['price']) ?></td>
                <td>
                    <span style="<?= $product['stock'] < 5 ? 'color: var(--error-color); font-weight: 600;' : '' ?>">
                        <?= $product['stock'] ?>
                    </span>
                </td>
                <td>
                    <a href="<?= BASE_URL ?>admin/product-form.php?id=<?= $product['id'] ?>" class="btn btn-primary" style="padding: 0.25rem 0.75rem;">
                        Sửa
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
