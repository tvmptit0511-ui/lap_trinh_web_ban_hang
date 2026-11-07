<?php
/**
 * Đổi mật khẩu
 * Yêu cầu: D11
 */

require_once __DIR__ . '/includes/init.php';

require_login();

$current_page = 'change-password';
$page_title = 'Đổi mật khẩu - ' . SITE_NAME;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Lấy thông tin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Validate
    if (empty($current_password)) {
        $errors['current_password'] = 'Vui lòng nhập mật khẩu hiện tại.';
    } elseif (!password_verify($current_password, $user['password'])) {
        // D11: bắt buộc nhập đúng mật khẩu hiện tại
        $errors['current_password'] = 'Mật khẩu hiện tại không đúng.';
    }

    if (empty($new_password)) {
        $errors['new_password'] = 'Vui lòng nhập mật khẩu mới.';
    } elseif (strlen($new_password) < 6) {
        $errors['new_password'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    }

    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
    }

    if (empty($errors)) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$password_hash, $_SESSION['user_id']])) {
            set_flash('success', 'Đã đổi mật khẩu thành công.');
            redirect(BASE_URL . 'profile.php');
        } else {
            $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Đổi mật khẩu</h1>

<div style="max-width: 500px; margin: 0 auto;">
    <?php if (isset($errors['general'])): ?>
        <div class="flash-message flash-error">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="current_password">Mật khẩu hiện tại <span style="color: var(--error-color);">*</span></label>
            <input type="password" id="current_password" name="current_password" class="form-control <?= isset($errors['current_password']) ? 'error' : '' ?>"
                   required minlength="6">
            <?php if (isset($errors['current_password'])): ?>
                <div class="error-message"><?= e($errors['current_password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="new_password">Mật khẩu mới <span style="color: var(--error-color);">*</span></label>
            <input type="password" id="new_password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'error' : '' ?>"
                   required minlength="6">
            <?php if (isset($errors['new_password'])): ?>
                <div class="error-message"><?= e($errors['new_password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirm_password">Xác nhận mật khẩu mới <span style="color: var(--error-color);">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'error' : '' ?>"
                   required minlength="6">
            <?php if (isset($errors['confirm_password'])): ?>
                <div class="error-message"><?= e($errors['confirm_password']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Đổi mật khẩu</button>
    </form>

    <p style="text-align: center; margin-top: 1rem;">
        <a href="<?= BASE_URL ?>profile.php">Quay lại trang tài khoản</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
