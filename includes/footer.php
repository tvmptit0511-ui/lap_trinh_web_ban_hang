        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <h3><?= e(SITE_NAME) ?></h3>
                    <p><?= e(SITE_SLOGAN) ?></p>
                    <p>Địa chỉ: 97 Man Thiện, Tăng Nhơn Phú, Hồ Chí Minh, Việt Nam</p>
                    <p>Điện thoại: 0365255070</p>
                    <p>Email: tvmptit0511@gmail.com</p>
                </div>

                <div class="footer-student">
                    <p><strong>Sinh viên thực hiện:</strong></p>
                    <p>Họ tên: <?= e(STUDENT_NAME) ?></p>
                    <p>MSSV: <?= e(STUDENT_ID) ?></p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. Đồ án môn Lập trình Web.</p>
            </div>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>js/main.js"></script>
</body>
</html>
