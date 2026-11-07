<?php
/**
 * Trang thông tin tài khoản
 * Yêu cầu: C7
 */

require_once __DIR__ . '/includes/init.php';

require_login();

$current_page = 'profile';
$page_title = 'Thông tin tài khoản - ' . SITE_NAME;

$errors = [];
$old = [];

// Lấy thông tin user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $old = compact('full_name', 'phone', 'address');

    // Validate
    if (empty($full_name)) {
        $errors['full_name'] = 'Vui lòng nhập họ tên.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users
            SET full_name = ?, phone = ?, address = ?
            WHERE id = ?
        ");

        if ($stmt->execute([$full_name, $phone, $address, $_SESSION['user_id']])) {
            $_SESSION['user_name'] = $full_name;
            set_flash('success', 'Đã cập nhật thông tin tài khoản.');
            redirect(BASE_URL . 'profile.php');
        } else {
            $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Thông tin tài khoản</h1>

<div style="max-width: 600px; margin: 0 auto;">
    <?php if (isset($errors['general'])): ?>
        <div class="flash-message flash-error">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
            <small style="color: var(--text-light);">Email không thể thay đổi</small>
        </div>

        <div class="form-group">
            <label for="full_name">Họ và tên <span style="color: var(--error-color);">*</span></label>
            <input type="text" id="full_name" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'error' : '' ?>"
                   value="<?= e($old['full_name'] ?? $user['full_name']) ?>" required>
            <?php if (isset($errors['full_name'])): ?>
                <div class="error-message"><?= e($errors['full_name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input type="tel" id="phone" name="phone" class="form-control"
                   value="<?= e($old['phone'] ?? $user['phone']) ?>">
        </div>

        <div class="form-group">
            <label for="address">Địa chỉ</label>
            <textarea id="address" name="address" class="form-control" rows="3"><?= e($old['address'] ?? $user['address']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Cập nhật thông tin</button>
    </form>

    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
        <a href="<?= BASE_URL ?>change-password.php" class="btn btn-secondary btn-block">Đổi mật khẩu</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
