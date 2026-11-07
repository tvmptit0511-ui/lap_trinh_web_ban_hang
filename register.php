<?php
/**
 * Trang đăng ký
 * Yêu cầu: A3, A7, C1
 */

require_once __DIR__ . '/includes/init.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (is_logged_in()) {
    redirect(BASE_URL);
}

$current_page = 'register';
$page_title = 'Đăng ký - ' . SITE_NAME;

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Lấy dữ liệu (C1: giữ lại dữ liệu đã nhập khi lỗi)
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $old = compact('email', 'full_name', 'phone', 'address');

    // Validate phía máy chủ (C1)
    if (empty($email)) {
        $errors['email'] = 'Vui lòng nhập email.';
    } elseif (!is_valid_email($email)) {
        $errors['email'] = 'Email không hợp lệ.';
    }

    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
    }

    if (empty($full_name)) {
        $errors['full_name'] = 'Vui lòng nhập họ tên.';
    }

    // Kiểm tra email trùng (C1)
    if (empty($errors['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email đã được sử dụng.';
        }
    }

    // Nếu không có lỗi, tạo tài khoản
    if (empty($errors)) {
        // Hash mật khẩu (C1)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (email, password, full_name, phone, address, role)
            VALUES (?, ?, ?, ?, ?, 'customer')
        ");

        if ($stmt->execute([$email, $password_hash, $full_name, $phone, $address])) {
            set_flash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
            redirect(BASE_URL . 'login.php');
        } else {
            $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 500px; margin: 0 auto;">
    <h1 class="page-title">Đăng ký tài khoản</h1>

    <?php if (isset($errors['general'])): ?>
        <div class="flash-message flash-error">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <!-- Form đăng ký - Yêu cầu A3 -->
    <form method="POST" action="">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">Email <span style="color: var(--error-color);">*</span></label>
            <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
                   value="<?= e($old['email'] ?? '') ?>" required>
            <?php if (isset($errors['email'])): ?>
                <div class="error-message"><?= e($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu <span style="color: var(--error-color);">*</span></label>
            <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'error' : '' ?>"
                   required minlength="6">
            <?php if (isset($errors['password'])): ?>
                <div class="error-message"><?= e($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirm_password">Xác nhận mật khẩu <span style="color: var(--error-color);">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'error' : '' ?>"
                   required minlength="6">
            <?php if (isset($errors['confirm_password'])): ?>
                <div class="error-message"><?= e($errors['confirm_password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="full_name">Họ và tên <span style="color: var(--error-color);">*</span></label>
            <input type="text" id="full_name" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'error' : '' ?>"
                   value="<?= e($old['full_name'] ?? '') ?>" required>
            <?php if (isset($errors['full_name'])): ?>
                <div class="error-message"><?= e($errors['full_name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input type="tel" id="phone" name="phone" class="form-control"
                   value="<?= e($old['phone'] ?? '') ?>" pattern="[0-9]{10,11}">
        </div>

        <div class="form-group">
            <label for="address">Địa chỉ</label>
            <textarea id="address" name="address" class="form-control" rows="3"><?= e($old['address'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
    </form>

    <p style="text-align: center; margin-top: 1rem;">
        Đã có tài khoản? <a href="<?= BASE_URL ?>login.php">Đăng nhập ngay</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
