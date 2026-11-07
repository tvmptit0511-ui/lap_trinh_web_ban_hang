<?php
/**
 * Trang chủ - Hiển thị danh sách sản phẩm
 * Yêu cầu: B4, B6, B7, D8 (phân trang)
 */

require_once __DIR__ . '/includes/init.php';

$current_page = 'home';
$page_title = SITE_NAME . ' - ' . SITE_SLOGAN;

// Lấy tham số lọc và tìm kiếm
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$page_num = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Xây dựng câu truy vấn
$where = ['1=1'];
$params = [];

if ($category_id > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $category_id;
}

if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = implode(' AND ', $where);

// Đếm tổng số sản phẩm (B7)
$count_sql = "SELECT COUNT(*) FROM products p WHERE $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_products = $stmt->fetchColumn();

// Xử lý sắp xếp (B6)
$order_by = 'p.created_at DESC';
if ($sort === 'price_asc') {
    $order_by = 'p.price ASC';
} elseif ($sort === 'price_desc') {
    $order_by = 'p.price DESC';
}

// Phân trang (D8)
$pagination = paginate($total_products, ITEMS_PER_PAGE, $page_num);

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE $where_clause
        ORDER BY $order_by
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh sách danh mục cho bộ lọc
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Tạo URL giữ tham số (D8)
function build_url($params_to_add) {
    global $category_id, $search, $sort;
    $params = [];

    if (isset($params_to_add['category'])) {
        $params['category'] = $params_to_add['category'];
    } elseif ($category_id > 0) {
        $params['category'] = $category_id;
    }

    if (isset($params_to_add['search'])) {
        $params['search'] = $params_to_add['search'];
    } elseif ($search !== '') {
        $params['search'] = $search;
    }

    if (isset($params_to_add['sort'])) {
        $params['sort'] = $params_to_add['sort'];
    } elseif ($sort !== '') {
        $params['sort'] = $sort;
    }

    if (isset($params_to_add['page'])) {
        $params['page'] = $params_to_add['page'];
    }

    return BASE_URL . 'index.php' . ($params ? '?' . http_build_query($params) : '');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="products-header">
    <div class="products-count">
        <!-- Yêu cầu B7: Hiển thị "Tìm thấy N sản phẩm" -->
        Tìm thấy <?= $total_products ?> sản phẩm
        <?php if ($category_id > 0): ?>
            trong danh mục <strong><?= e($products[0]['category_name'] ?? '') ?></strong>
        <?php endif; ?>
        <?php if ($search !== ''): ?>
            với từ khóa "<strong><?= e($search) ?></strong>"
        <?php endif; ?>
    </div>

    <div class="products-filter">
        <!-- Lọc theo danh mục (B4) -->
        <select name="category" onchange="window.location.href='<?= BASE_URL ?>?category=' + this.value + '<?= $search ? '&search=' . urlencode($search) : '' ?><?= $sort ? '&sort=' . $sort : '' ?>'">
            <option value="">Tất cả danh mục</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Tìm kiếm (B4) -->
        <form method="GET" action="" style="display: inline;">
            <?php if ($category_id > 0): ?>
                <input type="hidden" name="category" value="<?= $category_id ?>">
            <?php endif; ?>
            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?= e($search) ?>" class="form-control" style="width: 250px; display: inline-block;">
            <button type="submit" class="btn btn-primary">Tìm</button>
        </form>

                <!-- Sắp xếp theo giá (B6): PHP tạo URL sẵn vào value, JS chỉ điều hướng -->
        <select name="sort" onchange="window.location.href=this.value">
            <option value="<?= e(build_url(['sort' => '', 'page' => 1])) ?>" <?= $sort === '' ? 'selected' : '' ?>>
                Sắp xếp mặc định
            </option>
            <option value="<?= e(build_url(['sort' => 'price_asc', 'page' => 1])) ?>" <?= $sort === 'price_asc' ? 'selected' : '' ?>>
                Giá tăng dần
            </option>
            <option value="<?= e(build_url(['sort' => 'price_desc', 'page' => 1])) ?>" <?= $sort === 'price_desc' ? 'selected' : '' ?>>
                Giá giảm dần
            </option>
        </select>
</div>

<!-- Lưới sản phẩm (A4: Grid, A5: Responsive) -->
<div class="products-grid">
    <?php if (empty($products)): ?>
        <p class="text-center" style="grid-column: 1/-1;">Không tìm thấy sản phẩm nào.</p>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <a href="<?= BASE_URL ?>product.php?id=<?= $product['id'] ?>">
                    <img src="<?= BASE_URL ?>uploads/<?= e($product['image']) ?>"
                         alt="<?= e($product['name']) ?>"
                         class="product-image"
                         onerror="this.src='<?= BASE_URL ?>images/no-image.jpg'">
                </a>
                <div class="product-info">
                    <a href="<?= BASE_URL ?>product.php?id=<?= $product['id'] ?>" class="product-name">
                        <?= e($product['name']) ?>
                    </a>
                    <div class="product-price"><?= format_price($product['price']) ?></div>
                    <div class="product-stock">
                        <?php if ($product['stock'] > 0): ?>
                            Còn hàng: <?= $product['stock'] ?>
                        <?php else: ?>
                            <span style="color: var(--error-color);">Hết hàng</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($product['stock'] > 0): ?>
                        <form action="<?= BASE_URL ?>cart-add.php" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-block">Thêm vào giỏ</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Phân trang (D8) -->
<?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($pagination['current_page'] > 1): ?>
            <a href="<?= build_url(['page' => $pagination['current_page'] - 1]) ?>">« Trang trước</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
            <?php if ($i == $pagination['current_page']): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= build_url(['page' => $i]) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
            <a href="<?= build_url(['page' => $pagination['current_page'] + 1]) ?>">Trang sau »</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
