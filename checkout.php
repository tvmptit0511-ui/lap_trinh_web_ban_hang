<?php
/**
 * Trang đặt hàng (checkout)
 * Yêu cầu: C4 (giao dịch, trừ tồn kho)
 */

require_once __DIR__ . '/includes/init.php';

require_login();

$current_page = 'checkout';
$page_title = 'Đặt hàng - ' . SITE_NAME;

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    set_flash('error', 'Giỏ hàng trống.');
    redirect(BASE_URL . 'cart.php');
}

// Lấy thông tin user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $note = trim($_POST['note'] ?? '');

    $old = compact('customer_name', 'customer_phone', 'customer_address', 'note');

    // Validate
    if (empty($customer_name)) {
        $errors['customer_name'] = 'Vui lòng nhập họ tên.';
    }

    if (empty($customer_phone)) {
        $errors['customer_phone'] = 'Vui lòng nhập số điện thoại.';
    }

    if (empty($customer_address')) {
        $errors['customer_address'] = 'Vui lòng nhập địa chỉ.';
    }

    if (empty($errors)) {
        try {
            // Bắt đầu giao dịch (C4)
            $pdo->beginTransaction();

            // Lấy thông tin sản phẩm và tính tổng tiền (C4: giá từ CSDL)
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total_amount = 0;
            $order_items = [];

            foreach ($products as $product) {
                if (!isset($_SESSION['cart'][$product['id']])) {
                    continue;
                }

                $quantity = $_SESSION['cart'][$product['id']]['quantity'];

                // Kiểm tra tồn kho (C4)
                if ($product['stock'] < $quantity) {
                    throw new Exception("Sản phẩm '{$product['name']}' không đủ hàng.");
                }

                $subtotal = $product['price'] * $quantity;
                $total_amount += $subtotal;

                $order_items[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'price' => $product['price'], // C4: lưu đơn giá lúc mua
                    'quantity' => $quantity
                ];
            }

            // Tạo đơn hàng
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, status, customer_name, customer_phone, customer_address, note)
                VALUES (?, ?, 'pending', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $total_amount,
                $customer_name,
                $customer_phone,
                $customer_address,
                $note
            ]);

            $order_id = $pdo->lastInsertId();

            // Thêm chi tiết đơn hàng và trừ tồn kho (C4)
            $stmt_item = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt_update_stock = $pdo->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");

            foreach ($order_items as $item) {
                $stmt_item->execute([
                    $order_id,
                    $item['product_id'],
                    $item['product_name'],
                    $item['price'],
                    $item['quantity']
                ]);

                $stmt_update_stock->execute([
                    $item['quantity'],
                    $item['product_id']
                ]);
            }

            // Commit giao dịch (C4)
            $pdo->commit();

            // Xóa giỏ hàng
            $_SESSION['cart'] = [];

            set_flash('success', 'Đặt hàng thành công! Mã đơn hàng: #' . $order_id);
            redirect(BASE_URL . 'order-detail.php?id=' . $order_id);

        } catch (Exception $e) {
            // Rollback nếu có lỗi (C4)
            $pdo->rollBack();
            $errors['general'] = $e->getMessage();
        }
    }
}

// Tính tổng tiền hiện tại
$product_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($products as $product) {
    if (isset($_SESSION['cart'][$product['id']])) {
        $quantity = $_SESSION['cart'][$product['id']]['quantity'];
        $total += $product['price'] * $quantity;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Đặt hàng</h1>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <div>
        <h2 style="margin-bottom: 1rem;">Thông tin giao hàng</h2>

        <?php if (isset($errors['general'])): ?>
            <div class="flash-message flash-error">
                <?= e($errors['general']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="customer_name">Họ và tên người nhận <span style="color: var(--error-color);">*</span></label>
                <input type="text" id="customer_name" name="customer_name" class="form-control <?= isset($errors['customer_name']) ? 'error' : '' ?>"
                       value="<?= e($old['customer_name'] ?? $user['full_name']) ?>" required>
                <?php if (isset($errors['customer_name'])): ?>
                    <div class="error-message"><?= e($errors['customer_name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="customer_phone">Số điện thoại <span style="color: var(--error-color);">*</span></label>
                <input type="tel" id="customer_phone" name="customer_phone" class="form-control <?= isset($errors['customer_phone']) ? 'error' : '' ?>"
                       value="<?= e($old['customer_phone'] ?? $user['phone']) ?>" required>
                <?php if (isset($errors['customer_phone'])): ?>
                    <div class="error-message"><?= e($errors['customer_phone']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="customer_address">Địa chỉ giao hàng <span style="color: var(--error-color);">*</span></label>
                <textarea id="customer_address" name="customer_address" class="form-control <?= isset($errors['customer_address']) ? 'error' : '' ?>"
                          rows="3" required><?= e($old['customer_address'] ?? $user['address']) ?></textarea>
                <?php if (isset($errors['customer_address'])): ?>
                    <div class="error-message"><?= e($errors['customer_address']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="note">Ghi chú</label>
                <textarea id="note" name="note" class="form-control" rows="2"><?= e($old['note'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="font-size: 1.125rem;">
                Xác nhận đặt hàng
            </button>
        </form>
    </div>

    <div>
        <h2 style="margin-bottom: 1rem;">Đơn hàng của bạn</h2>

        <div style="background: var(--bg-gray); padding: 1.5rem; border-radius: 0.5rem;">
            <?php foreach ($products as $product): ?>
                <?php if (isset($_SESSION['cart'][$product['id']])): ?>
                    <?php $quantity = $_SESSION['cart'][$product['id']]['quantity']; ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <div>
                            <strong><?= e($product['name']) ?></strong>
                            <div style="color: var(--text-light); font-size: 0.875rem;">
                                <?= format_price($product['price']) ?> × <?= $quantity ?>
                            </div>
                        </div>
                        <div style="font-weight: 600;">
                            <?= format_price($product['price'] * $quantity) ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">

            <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700;">
                <span>Tổng cộng:</span>
                <span style="color: var(--primary-color);"><?= format_price($total) ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
