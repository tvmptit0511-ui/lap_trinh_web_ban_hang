<?php
/**
 * Trang lịch sử đơn hàng
 * Yêu cầu: C5 (chặn xem đơn người khác)
 */

require_once __DIR__ . '/includes/init.php';

require_login();

$current_page = 'orders';
$page_title = 'Đơn hàng của tôi - ' . SITE_NAME;

// Lấy danh sách đơn hàng của user hiện tại (C5)
$stmt = $pdo->prepare("
    SELECT * FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Đơn hàng của tôi</h1>

<?php if (empty($orders)): ?>
    <div style="text-align: center; padding: 3rem;">
        <p style="font-size: 1.125rem; color: var(--text-light); margin-bottom: 1.5rem;">
            Bạn chưa có đơn hàng nào
        </p>
        <a href="<?= BASE_URL ?>" class="btn btn-primary">Mua sắm ngay</a>
    </div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong>#<?= $order['id'] ?></strong></td>
                    <td><?= format_datetime($order['created_at']) ?></td>
                    <td><?= format_price($order['total_amount']) ?></td>
                    <td>
                        <span class="<?= get_order_status_class($order['status']) ?>">
                            <?= get_order_status_label($order['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>order-detail.php?id=<?= $order['id'] ?>" class="btn btn-primary" style="padding: 0.25rem 0.75rem;">
                            Xem chi tiết
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
