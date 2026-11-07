<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?= BASE_URL ?>">
                        <img src="<?= BASE_URL ?>images/favicon.ico" alt="<?= e(SITE_NAME) ?>">
                        <div class="logo-text">
                            <h1><?= e(SITE_NAME) ?></h1>
                            <p><?= e(SITE_SLOGAN) ?></p>
                        </div>
                    </a>
                </div>

                <nav class="main-nav">
                    <ul>
                        <li><a href="<?= BASE_URL ?>" class="<?= ($current_page ?? '') === 'home' ? 'active' : '' ?>">Trang chủ</a></li>
                        <li><a href="<?= BASE_URL ?>about.php" class="<?= ($current_page ?? '') === 'about' ? 'active' : '' ?>">Giới thiệu</a></li>
                        <li><a href="<?= BASE_URL ?>contact.php" class="<?= ($current_page ?? '') === 'contact' ? 'active' : '' ?>">Liên hệ</a></li>

                        <?php if (is_logged_in()): ?>
                            <li><a href="<?= BASE_URL ?>cart.php" class="<?= ($current_page ?? '') === 'cart' ? 'active' : '' ?>">
                                Giỏ hàng
                                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                    <span class="cart-count"><?= count($_SESSION['cart']) ?></span>
                                <?php endif; ?>
                            </a></li>
                            <li><a href="<?= BASE_URL ?>orders.php" class="<?= ($current_page ?? '') === 'orders' ? 'active' : '' ?>">Đơn hàng</a></li>
                            <li class="dropdown">
                                <a href="#" class="<?= in_array($current_page ?? '', ['profile', 'change-password']) ? 'active' : '' ?>">
                                    <?= e($_SESSION['user_name']) ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= BASE_URL ?>profile.php">Tài khoản</a></li>
                                    <li><a href="<?= BASE_URL ?>change-password.php">Đổi mật khẩu</a></li>
                                    <?php if (is_admin()): ?>
                                        <li><a href="<?= BASE_URL ?>admin/">Quản trị</a></li>
                                    <?php endif; ?>
                                    <li><a href="<?= BASE_URL ?>logout.php">Đăng xuất</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li><a href="<?= BASE_URL ?>register.php" class="<?= ($current_page ?? '') === 'register' ? 'active' : '' ?>">Đăng ký</a></li>
                            <li><a href="<?= BASE_URL ?>login.php" class="<?= ($current_page ?? '') === 'login' ? 'active' : '' ?>">Đăng nhập</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <?php if ($flash = get_flash()): ?>
        <div class="flash-message flash-<?= e($flash['type']) ?>">
            <div class="container">
                <?= e($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">
