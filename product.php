<?php
/**
 * Trang chi tiết sản phẩm
 * Yêu cầu: A2 (bảng thông số), B5 (404 khi không có), B7 (sản phẩm cùng danh mục)
 */

require_once __DIR__ . '/includes/init.php';

$current_page = 'product';

// Lấy ID sản phẩm
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

// Lấy thông tin sản phẩm (B5)
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Nếu không tìm thấy, trả về 404 (B5)
if (!$product) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$page_title = e($product['name']) . ' - ' . SITE_NAME;

// Lấy tối đa 3 sản phẩm cùng danh mục (B7)
$stmt = $pdo->prepare("
    SELECT * FROM products
    WHERE category_id = ? AND id != ?
    ORDER BY RAND()
    LIMIT 3
");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="product-detail">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <img src="<?= BASE_URL ?>uploads/<?= e($product['image']) ?>"
                 alt="<?= e($product['name']) ?>"
                 style="width: 100%; border-radius: 0.5rem;"
                 onerror="this.src='<?= BASE_URL ?>images/no-image.jpg'">
        </div>

        <div>
            <h1 class="page-title"><?= e($product['name']) ?></h1>

            <div class="product-price" style="font-size: 2rem; margin: 1rem 0;">
                <?= format_price($product['price']) ?>
            </div>

            <div class="product-stock" style="margin-bottom: 1.5rem;">
                <?php if ($product['stock'] > 0): ?>
                    <span style="color: var(--success-color); font-weight: 600;">
                        ✓ Còn hàng (<?= $product['stock'] ?> sản phẩm)
                    </span>
                <?php else: ?>
                    <span style="color: var(--error-color); font-weight: 600;">
                        ✗ Hết hàng
                    </span>
                <?php endif; ?>
            </div>

            <div class="product-description" style="margin-bottom: 1.5rem; line-height: 1.8;">
                <?= nl2br(e($product['description'])) ?>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <form action="<?= BASE_URL ?>cart-add.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <div class="form-group">
                        <label>Số lượng:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>"
                               class="form-control" style="width: 100px;">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="font-size: 1.125rem; padding: 1rem;">
                        Thêm vào giỏ hàng
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bảng thông số kỹ thuật (A2) -->
    <h2 style="margin-top: 3rem; margin-bottom: 1rem;">Thông số kỹ thuật</h2>
    <table class="table">
        <tbody>
            <tr>
                <th style="width: 200px;">Tên sản phẩm</th>
                <td><?= e($product['name']) ?></td>
            </tr>
            <tr>
                <th>Danh mục</th>
                <td><?= e($product['category_name']) ?></td>
            </tr>
            <tr>
                <th>Giá bán</th>
                <td><?= format_price($product['price']) ?></td>
            </tr>
            <tr>
                <th>Tình trạng</th>
                <td>
                    <?php if ($product['stock'] > 0): ?>
                        Còn hàng (<?= $product['stock'] ?> sản phẩm)
                    <?php else: ?>
                        Hết hàng
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Mã sản phẩm</th>
                <td>SP<?= str_pad($product['id'], 6, '0', STR_PAD_LEFT) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Sản phẩm cùng danh mục (B7) -->
    <?php if (!empty($related_products)): ?>
        <h2 style="margin-top: 3rem; margin-bottom: 1rem;">Sản phẩm cùng danh mục</h2>
        <div class="products-grid" style="grid-template-columns: repeat(3, 1fr);">
            <?php foreach ($related_products as $related): ?>
                <div class="product-card">
                    <a href="<?= BASE_URL ?>product.php?id=<?= $related['id'] ?>">
                        <img src="<?= BASE_URL ?>uploads/<?= e($related['image']) ?>"
                             alt="<?= e($related['name']) ?>"
                             class="product-image"
                             onerror="this.src='<?= BASE_URL ?>images/no-image.jpg'">
                    </a>
                    <div class="product-info">
                        <a href="<?= BASE_URL ?>product.php?id=<?= $related['id'] ?>" class="product-name">
                            <?= e($related['name']) ?>
                        </a>
                        <div class="product-price"><?= format_price($related['price']) ?></div>
                        <?php if ($related['stock'] > 0): ?>
                            <form action="<?= BASE_URL ?>cart-add.php" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $related['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-block">Thêm vào giỏ</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
