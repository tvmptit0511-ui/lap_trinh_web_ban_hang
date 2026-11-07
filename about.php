<?php
/**
 * Trang giới thiệu
 * Yêu cầu: A2 (bảng thông số)
 */

require_once __DIR__ . '/includes/init.php';

$current_page = 'about';
$page_title = 'Giới thiệu - ' . SITE_NAME;

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Giới thiệu về <?= e(SITE_NAME) ?></h1>

<div style="line-height: 1.8;">
    <p>
        Chào mừng bạn đến với <strong><?= e(SITE_NAME) ?></strong> - địa chỉ tin cậy cho mọi nhu cầu về điện thoại và phụ kiện chính hãng.
    </p>

    <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Về chúng tôi</h2>
    <p>
        <?= e(SITE_NAME) ?> được thành lập với sứ mệnh mang đến cho khách hàng những sản phẩm công nghệ chất lượng cao với giá cả hợp lý nhất.
        Chúng tôi cam kết cung cấp sản phẩm chính hãng 100%, bảo hành đầy đủ theo chính sách của nhà sản xuất.
    </p>

    <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Thông tin cửa hàng</h2>

    <!-- Bảng thông số - Yêu cầu A2 -->
    <table class="table">
        <tbody>
            <tr>
                <th style="width: 200px;">Tên cửa hàng</th>
                <td><?= e(SITE_NAME) ?></td>
            </tr>
            <tr>
                <th>Chủ sở hữu</th>
                <td><?= e(STUDENT_NAME) ?></td>
            </tr>
            <tr>
                <th>Địa chỉ</th>
                <td>123 Đường Trần Đại Nghĩa, Q.Hai Bà Trưng, Hà Nội</td>
            </tr>
            <tr>
                <th>Điện thoại</th>
                <td>0901234567</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>contact@tranvanmy.com</td>
            </tr>
            <tr>
                <th>Giờ làm việc</th>
                <td>8:00 - 22:00 (Thứ 2 - Chủ Nhật)</td>
            </tr>
            <tr>
                <th>Chuyên môn</th>
                <td><?= e(SITE_SLOGAN) ?></td>
            </tr>
        </tbody>
    </table>

    <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Cam kết của chúng tôi</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Sản phẩm chính hãng</h3>
            <p>100% sản phẩm chính hãng, có đầy đủ giấy tờ, tem bảo hành từ nhà sản xuất.</p>
        </div>
        <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Giá cả hợp lý</h3>
            <p>Giá bán cạnh tranh nhất thị trường với nhiều chương trình khuyến mãi hấp dẫn.</p>
        </div>
        <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Giao hàng nhanh</h3>
            <p>Giao hàng toàn quốc, nhanh chóng trong 1-3 ngày làm việc.</p>
        </div>
        <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Hỗ trợ tận tâm</h3>
            <p>Đội ngũ tư vấn chuyên nghiệp, nhiệt tình, sẵn sàng hỗ trợ 24/7.</p>
        </div>
    </div>

    <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Thông tin sinh viên thực hiện</h2>
    <table class="table">
        <tbody>
            <tr>
                <th style="width: 200px;">Họ và tên</th>
                <td><?= e(STUDENT_NAME) ?></td>
            </tr>
            <tr>
                <th>Mã số sinh viên</th>
                <td><?= e(STUDENT_ID) ?></td>
            </tr>
            <tr>
                <th>Trường</th>
                <td>Học viện Công nghệ Bưu chính Viễn thông</td>
            </tr>
            <tr>
                <th>Môn học</th>
                <td>Lập trình Web: HTML, CSS, JavaScript, PHP</td>
            </tr>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
