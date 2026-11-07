<?php
/**
 * Trang đăng nhập
 * Yêu cầu: A3, C2, D10 (khóa sau 5 lần sai)
 */

require_once __DIR__ . '/includes/init.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (is_logged_in()) {
    redirect(BASE_URL);
}

$current_page = 'login';
$page_title = 'Đăng nhập - ' . SITE_NAME;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        // Kiểm tra khóa đăng nhập (D10)
        check_login_attempts();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new Exception('Vui lòng nhập đầy đủ thông tin.');
        }

        // Tìm user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Kiểm tra mật khẩu (C2)
        if (!$user || !password_verify($password, $user['password'])) {
            record_failed_login();
            throw new Exception('Email hoặc mật khẩu không đúng.');
        }

        // Đăng nhập thành công
        reset_login_attempts();

        // Tạo session (C2)
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        set_flash('success', 'Đăng nhập thành công!');

        // Chuyển về trang định vào trước đó (C9)
        $redirect_to = $_SESSION['redirect_after_login'] ?? BASE_URL;
        unset($_SESSION['redirect_after_login']);
        redirect($redirect_to);

    } catch (Exception $e) {
        $errors['general'] = $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 400px; margin: 0 auto;">
    <h1 class="page-title">Đăng nhập</h1>

    <?php if (isset($errors['general'])): ?>
        <div class="flash-message flash-error">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <!-- Form đăng nhập - Yêu cầu A3 -->
    <form method="POST" action="">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">Email <span style="color: var(--error-color);">*</span></label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= e($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu <span style="color: var(--error-color);">*</span></label>
            <input type="password" id="password" name="password" class="form-control" required minlength="6">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
    </form>

    <p style="text-align: center; margin-top: 1rem;">
        Chưa có tài khoản? <a href="<?= BASE_URL ?>register.php">Đăng ký ngay</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
