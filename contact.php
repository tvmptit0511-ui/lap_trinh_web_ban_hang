<?php
/**
 * Trang liên hệ
 * Yêu cầu: A2 (bảng thông số), A7, C8 (lưu vào bảng contacts)
 */

require_once __DIR__ . '/includes/init.php';

$current_page = 'contact';
$page_title = 'Liên hệ - ' . SITE_NAME;

$errors = [];
$old = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $old = compact('name', 'email', 'subject', 'message');

    // Validate
    if (empty($name)) {
        $errors['name'] = 'Vui lòng nhập họ tên.';
    }

    if (empty($email)) {
        $errors['email'] = 'Vui lòng nhập email.';
    } elseif (!is_valid_email($email)) {
        $errors['email'] = 'Email không hợp lệ.';
    }

    if (empty($subject)) {
        $errors['subject'] = 'Vui lòng nhập tiêu đề.';
    }

    if (empty($message)) {
        $errors['message'] = 'Vui lòng nhập nội dung.';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Nội dung phải có ít nhất 10 ký tự.';
    }

    // Lưu vào database (C8)
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO contacts (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ");

        if ($stmt->execute([$name, $email, $subject, $message])) {
            $success = true;
            $old = [];
        } else {
            $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <h1 class="page-title">Liên hệ với chúng tôi</h1>

    <?php if ($success): ?>
        <div class="flash-message flash-success">
            Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <h2 style="margin-bottom: 1rem;">Thông tin liên hệ</h2>

            <!-- Bảng thông số - Yêu cầu A2 -->
            <table class="table">
                <tbody>
                    <tr>
                        <th style="width: 120px;">Địa chỉ</th>
                        <td>97 Man Thiện, Tăng Nhơn Phú, Hồ Chí Minh, Việt Nam</td>
                    </tr>
                    <tr>
                        <th>Điện thoại</th>
                        <td>0365255070</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>tvmptit0511@gmail.com</td>
                    </tr>
                    <tr>
                        <th>Giờ làm việc</th>
                        <td>8:00 - 22:00 hàng ngày</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div>
            <h2 style="margin-bottom: 1rem;">Gửi tin nhắn</h2>

            <?php if (isset($errors['general'])): ?>
                <div class="flash-message flash-error">
                    <?= e($errors['general']) ?>
                </div>
            <?php endif; ?>

            <!-- Form liên hệ - Yêu cầu A3, A7 -->
            <form method="POST" action="">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="name">Họ và tên <span style="color: var(--error-color);">*</span></label>
                    <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'error' : '' ?>"
                           value="<?= e($old['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="error-message"><?= e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color: var(--error-color);">*</span></label>
                    <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
                           value="<?= e($old['email'] ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="subject">Tiêu đề <span style="color: var(--error-color);">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control <?= isset($errors['subject']) ? 'error' : '' ?>"
                           value="<?= e($old['subject'] ?? '') ?>" required>
                    <?php if (isset($errors['subject'])): ?>
                        <div class="error-message"><?= e($errors['subject']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="message">Nội dung <span style="color: var(--error-color);">*</span></label>
                    <textarea id="message" name="message" class="form-control <?= isset($errors['message']) ? 'error' : '' ?>"
                              rows="5" required minlength="10"><?= e($old['message'] ?? '') ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <div class="error-message"><?= e($errors['message']) ?></div>
                    <?php endif; ?>
                    <small style="color: var(--text-light);">Tối thiểu 10 ký tự</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Gửi tin nhắn</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
