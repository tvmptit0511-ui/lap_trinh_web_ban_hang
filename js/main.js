/**
 * JavaScript cho website Cửa hàng Trần Văn Mỹ
 * MSSV: N25DTCN035
 * Yêu cầu A7, A8: Validation, tính toán giỏ hàng
 */

// Yêu cầu A7: Kiểm tra hai mật khẩu khớp nhau
document.addEventListener('DOMContentLoaded', function() {
    // Form đăng ký - kiểm tra mật khẩu
    const registerForm = document.querySelector('form[action*="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Mật khẩu xác nhận không khớp!');
                    confirmPassword.focus();
                    return false;
                }
            }
        });
    }

    // Form liên hệ - chặn nội dung dưới 10 ký tự (Yêu cầu A7)
    const contactForm = document.querySelector('form[action*="contact"]');
    if (contactForm) {
        const messageField = contactForm.querySelector('textarea[name="message"]');
        if (messageField) {
            contactForm.addEventListener('submit', function(e) {
                if (messageField.value.trim().length < 10) {
                    e.preventDefault();
                    alert('Nội dung tin nhắn phải có ít nhất 10 ký tự!');
                    messageField.focus();
                    return false;
                }
            });
        }
    }

    // Yêu cầu A8: Giỏ hàng tính lại thành tiền ngay khi đổi số lượng
    const cartForm = document.querySelector('.cart-form');
    if (cartForm) {
        const quantityInputs = cartForm.querySelectorAll('input[type="number"][name^="quantity"]');

        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                updateCartItem(this);
            });
        });

        // Nút xóa giỏ hàng có hộp xác nhận (Yêu cầu A8)
        const clearCartBtn = document.querySelector('.btn-clear-cart');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', function(e) {
                if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng không?')) {
                    e.preventDefault();
                    return false;
                }
            });
        }

        // Nút xóa từng sản phẩm
        const deleteButtons = cartForm.querySelectorAll('.btn-delete-item');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }
});

// Cập nhật giỏ hàng khi thay đổi số lượng
function updateCartItem(input) {
    const productId = input.getAttribute('data-product-id');
    const quantity = parseInt(input.value);
    const price = parseFloat(input.getAttribute('data-price'));

    if (quantity < 1) {
        input.value = 1;
        return;
    }

    // Tính thành tiền
    const subtotal = quantity * price;
    const subtotalElement = input.closest('tr').querySelector('.item-subtotal');
    if (subtotalElement) {
        subtotalElement.textContent = formatPrice(subtotal);
    }

    // Cập nhật tổng tiền
    updateCartTotal();

    // Gửi AJAX request để cập nhật session (nếu cần)
    // Ở đây chúng ta tự động submit form
    const form = input.closest('form');
    if (form) {
        // Tạo hidden input để đánh dấu là update
        const updateInput = document.createElement('input');
        updateInput.type = 'hidden';
        updateInput.name = 'update_cart';
        updateInput.value = '1';
        form.appendChild(updateInput);

        // Auto-submit sau 500ms (debounce)
        clearTimeout(input.updateTimer);
        input.updateTimer = setTimeout(() => {
            form.submit();
        }, 500);
    }
}

// Cập nhật tổng tiền giỏ hàng
function updateCartTotal() {
    const subtotalElements = document.querySelectorAll('.item-subtotal');
    let total = 0;

    subtotalElements.forEach(el => {
        const value = parseFloat(el.textContent.replace(/[^\d]/g, ''));
        if (!isNaN(value)) {
            total += value;
        }
    });

    const totalElement = document.querySelector('.cart-total-amount');
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
    }
}

// Format giá tiền
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

// Xác nhận xóa
function confirmDelete(message) {
    return confirm(message || 'Bạn có chắc chắn muốn xóa?');
}

// Preview ảnh khi upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('image-preview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Dropdown menu cho mobile
document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
});
