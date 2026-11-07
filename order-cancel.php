<?php
/**
 * Hủy đơn hàng
 * Yêu cầu: C6 (hủy đơn, cộng trả tồn kho trong giao dịch)
 */

require_once __DIR__ . '/includes/init.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'orders.php');
}

verify_csrf();

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if ($order_id <= 0) {
    set_flash('error', 'Đơn hàng không hợp lệ.');
    redirect(BASE_URL . 'orders.php');
}

try {
    // Bắt đầu giao dịch (C6)
    $pdo->beginTransaction();

    // Kiểm tra đơn hàng và quyền sở hữu
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại hoặc bạn không có quyền hủy.');
    }

    // Chỉ được hủy đơn "Chờ xử lý" (C6)
    if ($order['status'] !== 'pending') {
        throw new Exception('Chỉ có thể hủy đơn hàng đang chờ xử lý.');
    }

    // Cập nhật trạng thái đơn hàng
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);

    // Lấy danh sách sản phẩm trong đơn
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    // Cộng trả tồn kho (C6)
    $stmt_restore = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");

    foreach ($items as $item) {
        $stmt_restore->execute([
            $item['quantity'],
            $item['product_id']
        ]);
    }

    // Commit giao dịch (C6)
    $pdo->commit();

    set_flash('success', 'Đã hủy đơn hàng thành công. Số lượng sản phẩm đã được hoàn trả.');
    redirect(BASE_URL . 'order-detail.php?id=' . $order_id);

} catch (Exception $e) {
    // Rollback nếu có lỗi
    $pdo->rollBack();
    set_flash('error', $e->getMessage());
    redirect(BASE_URL . 'orders.php');
}
