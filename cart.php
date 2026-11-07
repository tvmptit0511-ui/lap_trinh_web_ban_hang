<?php
/**
 * Trang giỏ hàng
 * Yêu cầu: A8, C3
 */

require_once __DIR__ . '/includes/init.php';

$current_page = 'cart';
$page_title = 'Giỏ hàng - ' . SITE_NAME;

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý cập nhật giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    verify_csrf();

    foreach ($_POST['quantity'] as $product_id => $quantity) {
        $product_id = (int)$product_id;
        $quantity = max(0, (int)$quantity);

        if ($quantity == 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
    }

    set_flash('success', 'Đã cập nhật giỏ hàng.');
    redirect(BASE_URL . 'cart.php');
}

// Xử lý xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    verify_csrf();
    $product_id = (int)$_POST['product_id'];
    unset($_SESSION['cart'][$product_id]);
    set_flash('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    redirect(BASE_URL . 'cart.php');
}

// Xử lý xóa toàn bộ giỏ hàng (A8)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cart'])) {
    verify_csrf();
    $_SESSION['cart'] = [];
    set_flash('success', 'Đã xóa toàn bộ giỏ hàng.');
    redirect(BASE_URL . 'cart.php');
}

// Lấy thông tin sản phẩm trong giỏ (C3: giá luôn lấy từ CSDL)
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        if (isset($_SESSION['cart'][$product['id']])) {
            $quantity = $_SESSION['cart'][$product['id']]['quantity'];
            $subtotal = $product['price'] * $quantity;

            $cart_items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];

            $total += $subtotal;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Giỏ hàng của bạn</h1>

<?php if (empty($cart_items)): ?>
    <div style="text-align: center; padding: 3rem;">
        <p style="font-size: 1.125rem; color: var(--text-light); margin-bottom: 1.5rem;">
            Giỏ hàng của bạn đang trống
        </p>
        <a href="<?= BASE_URL ?>" class="btn btn-primary">Tiếp tục mua sắm</a>
    </div>
<?php else: ?>
    <!-- Form giỏ hàng với JavaScript tính toán (A8) -->
    <form method="POST" action="" class="cart-form">
        <?= csrf_field() ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="width: 150px;">Đơn giá</th>
                    <th style="width: 120px;">Số lượng</th>
                    <th style="width: 150px;">Thành tiền</th>
                    <th style="width: 100px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <img src="<?= BASE_URL ?>uploads/<?= e($item['product']['image']) ?>"
                                     alt="<?= e($item['product']['name']) ?>"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.25rem;"
                                     onerror="this.src='<?= BASE_URL ?>images/no-image.jpg'">
                                <a href="<?= BASE_URL ?>product.php?id=<?= $item['product']['id'] ?>">
                                    <?= e($item['product']['name']) ?>
                                </a>
                            </div>
                        </td>
                        <td><?= format_price($item['product']['price']) ?></td>
                        <td>
                            <!-- Yêu cầu A8: tính lại khi đổi số lượng -->
                            <input type="number"
                                   name="quantity[<?= $item['product']['id'] ?>]"
                                   value="<?= $item['quantity'] ?>"
                                   min="1"
                                   max="<?= $item['product']['stock'] ?>"
                                   class="form-control"
                                   data-product-id="<?= $item['product']['id'] ?>"
                                   data-price="<?= $item['product']['price'] ?>"
                                   style="width: 80px;">
                        </td>
                        <td class="item-subtotal"><?= format_price($item['subtotal']) ?></td>
                        <td>
                            <button type="submit" name="remove_item" value="1"
                                    class="btn btn-danger btn-delete-item"
                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                Xóa
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
            <button type="submit" name="clear_cart" value="1" class="btn btn-danger btn-clear-cart">
                Xóa toàn bộ giỏ hàng
            </button>
            <button type="submit" name="update_cart" value="1" class="btn btn-secondary">
                Cập nhật giỏ hàng
            </button>
        </div>
    </form>

    <!-- Tổng tiền -->
    <div class="cart-summary">
        <div class="cart-total">
            <span>Tổng tiền:</span>
            <span class="cart-total-amount"><?= format_price($total) ?></span>
        </div>

        <?php if (is_logged_in()): ?>
            <a href="<?= BASE_URL ?>checkout.php" class="btn btn-primary btn-block" style="font-size: 1.125rem;">
                Tiến hành đặt hàng
            </a>
        <?php else: ?>
            <p style="text-align: center; margin-bottom: 1rem;">
                Vui lòng <a href="<?= BASE_URL ?>login.php">đăng nhập</a> để đặt hàng
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
