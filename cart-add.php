<?php
/**
 * Thêm sản phẩm vào giỏ hàng
 * Yêu cầu: C3
 */

require_once __DIR__ . '/includes/init.php';

verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL);
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if ($product_id <= 0) {
    set_flash('error', 'Sản phẩm không hợp lệ.');
    redirect(BASE_URL);
}

// Lấy thông tin sản phẩm (C3: giá luôn lấy từ CSDL)
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Sản phẩm không tồn tại.');
    redirect(BASE_URL);
}

if ($product['stock'] < $quantity) {
    set_flash('error', 'Không đủ hàng trong kho.');
    redirect(BASE_URL . 'product.php?id=' . $product_id);
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Thêm hoặc cập nhật số lượng (C3)
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = [
        'product_id' => $product_id,
        'quantity' => $quantity
    ];
}

set_flash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
redirect(BASE_URL . 'cart.php');
