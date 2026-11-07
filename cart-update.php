<?php
/**
 * Cập nhật giỏ hàng: đổi số lượng / xóa sản phẩm / xóa toàn bộ giỏ
 * Yêu cầu: C3, A8 (xác nhận xóa giỏ)
 */

require_once __DIR__ . '/includes/init.php';
verify_csrf();

// Xóa toàn bộ giỏ hàng (A8: có confirm ở phía client)
if (isset($_POST['action']) && $_POST['action'] === 'clear') {
    unset($_SESSION['cart']);
    set_flash('success', 'Đã xóa toàn bộ giỏ hàng.');
    redirect(BASE_URL . 'cart.php');
}

// Cập nhật hoặc xóa một sản phẩm
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    set_flash('error', 'Dữ liệu không hợp lệ.');
    redirect(BASE_URL . 'cart.php');
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

// Kiểm tra sản phẩm tồn tại và lấy giá từ CSDL (C3: giá luôn từ CSDL)
$stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Sản phẩm không tồn tại.');
    redirect(BASE_URL . 'cart.php');
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xóa sản phẩm nếu quantity = 0
if ($quantity <= 0) {
    unset($_SESSION['cart'][$product_id]);
    set_flash('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    redirect(BASE_URL . 'cart.php');
}

// Kiểm tra tồn kho
if ($quantity > $product['stock']) {
    set_flash('error', "Chỉ còn {$product['stock']} sản phẩm trong kho.");
    redirect(BASE_URL . 'cart.php');
}

// Cập nhật số lượng
$_SESSION['cart'][$product_id] = [
    'id' => $product['id'],
    'name' => $product['name'],
    'price' => $product['price'],
    'quantity' => $quantity
];

set_flash('success', 'Đã cập nhật giỏ hàng.');
redirect(BASE_URL . 'cart.php');