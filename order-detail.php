<?php
/**
 * Trang chi tiết đơn hàng
 * Yêu cầu: C5 (chặn xem đơn người khác)
 */

require_once __DIR__ . '/includes/init.php';

require_login();

$current_page = 'orders';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    set_flash('error', 'Đơn hàng không hợp lệ.');
    redirect(BASE_URL . 'orders.php');
}

// Lấy thông tin đơn hàng (C5: chỉ lấy của user hiện tại)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

// Nếu không tìm thấy hoặc không phải đơn của user, chặn (C5)
if (!$order) {
    http_response_code(403);
    die('Bạn không có quyền xem đơn hàng này.');
}

// Lấy chi tiết sản phẩm trong đơn
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$page_title = 'Đơn hàng #' . $order_id . ' - ' . SITE_NAME;

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Chi tiết đơn hàng #<?= $order['id'] ?></h1>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <div>
        <h2 style="margin-bottom: 1rem;">Sản phẩm</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="width: 150px;">Đơn giá</th>
                    <th style="width: 100px;">Số lượng</th>
                    <th style="width: 150px;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['product_name']) ?></td>
                        <td><?= format_price($item['price']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= format_price($item['price'] * $item['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: 600;">Tổng cộng:</td>
                    <td style="font-weight: 700; color: var(--primary-color); font-size: 1.125rem;">
                        <?= format_price($order['total_amount']) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div>
        <h2 style="margin-bottom: 1rem;">Thông tin đơn hàng</h2>

        <div style="background: var(--bg-gray); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem;">
                <strong>Trạng thái:</strong><br>
                <span class="<?= get_order_status_class($order['status']) ?>" style="margin-top: 0.5rem; display: inline-block;">
                    <?= get_order_status_label($order['status']) ?>
                </span>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong>Ngày đặt:</strong><br>
                <?= format_datetime($order['created_at']) ?>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong>Người nhận:</strong><br>
                <?= e($order['customer_name']) ?>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong>Số điện thoại:</strong><br>
                <?= e($order['customer_phone']) ?>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong>Địa chỉ:</strong><br>
                <?= nl2br(e($order['customer_address'])) ?>
            </div>

            <?php if ($order['note']): ?>
                <div style="margin-bottom: 1rem;">
                    <strong>Ghi chú:</strong><br>
                    <?= nl2br(e($order['note'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($order['status'] === 'pending'): ?>
            <form method="POST" action="<?= BASE_URL ?>order-cancel.php" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <button type="submit" class="btn btn-danger btn-block">Hủy đơn hàng</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
