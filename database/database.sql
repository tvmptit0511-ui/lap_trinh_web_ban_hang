-- Database: minishop
-- Cơ sở dữ liệu cho website bán hàng Cửa hàng Trần Văn Mỹ
-- MSSV: N25DTCN035

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Tạo database
CREATE DATABASE IF NOT EXISTS `minishop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `minishop`;

-- Bảng categories (danh mục sản phẩm)
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng products (sản phẩm)
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng users (người dùng)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng orders (đơn hàng)
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng order_items (chi tiết đơn hàng)
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng contacts (liên hệ)
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu: Categories
INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Điện thoại', 'dien-thoai'),
(2, 'Tai nghe', 'tai-nghe'),
(3, 'Phụ kiện', 'phu-kien');

-- Thêm dữ liệu mẫu: Products (12 sản phẩm)
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `image`) VALUES
(1, 1, 'iPhone 15 Pro Max 256GB', 'iphone-15-pro-max-256gb', 'iPhone 15 Pro Max với chip A17 Pro mạnh mẽ, camera 48MP, màn hình Super Retina XDR 6.7 inch, hỗ trợ 5G và nhiều tính năng cao cấp khác.', 29990000.00, 15, 'iphone-15-pro-max.jpg'),
(2, 1, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'Samsung Galaxy S24 Ultra với màn hình Dynamic AMOLED 2X 6.8 inch, bộ xử lý Snapdragon 8 Gen 3, camera 200MP, pin 5000mAh và bút S Pen tích hợp.', 27990000.00, 12, 'samsung-s24-ultra.jpg'),
(3, 1, 'Xiaomi 14 Pro 512GB', 'xiaomi-14-pro-512gb', 'Xiaomi 14 Pro màn hình AMOLED 6.73 inch 120Hz, chip Snapdragon 8 Gen 3, camera Leica 50MP, sạc nhanh 120W, thiết kế cao cấp.', 18990000.00, 20, 'xiaomi-14-pro.jpg'),
(4, 1, 'OPPO Find X7 Ultra', 'oppo-find-x7-ultra', 'OPPO Find X7 Ultra với hệ thống camera Hasselblad, màn hình AMOLED 2K 120Hz, chip Snapdragon 8 Gen 3, pin 5000mAh sạc siêu nhanh 100W.', 24990000.00, 8, 'oppo-find-x7.jpg'),
(5, 2, 'AirPods Pro Gen 2', 'airpods-pro-gen-2', 'Tai nghe AirPods Pro thế hệ 2 với chip H2, chống ồn chủ động nâng cao, âm thanh spatial audio, chống nước IPX4, thời lượng pin lên đến 6 giờ.', 5990000.00, 30, 'airpods-pro-2.jpg'),
(6, 2, 'Sony WH-1000XM5', 'sony-wh-1000xm5', 'Tai nghe Sony WH-1000XM5 over-ear, chống ồn hàng đầu, âm thanh Hi-Res, pin 30 giờ, kết nối Bluetooth 5.2, thiết kế cao cấp thoải mái.', 7990000.00, 18, 'sony-wh-1000xm5.jpg'),
(7, 2, 'Samsung Galaxy Buds2 Pro', 'samsung-galaxy-buds2-pro', 'Tai nghe true wireless Samsung Galaxy Buds2 Pro với âm thanh 360 độ, chống ồn thông minh, chống nước IPX7, thời lượng pin 8 giờ khi bật ANC.', 3990000.00, 25, 'galaxy-buds2-pro.jpg'),
(8, 2, 'JBL Tune 760NC', 'jbl-tune-760nc', 'Tai nghe JBL Tune 760NC on-ear với chống ồn chủ động, âm thanh JBL Pure Bass, pin 50 giờ, sạc nhanh, thiết kế gập gọn tiện lợi.', 1990000.00, 35, 'jbl-tune-760nc.jpg'),
(9, 3, 'Ốp lưng Spigen iPhone 15 Pro', 'op-lung-spigen-iphone-15-pro', 'Ốp lưng Spigen chính hãng cho iPhone 15 Pro, chất liệu TPU + PC cao cấp, chống sốc Military Grade, viền bảo vệ camera, độ bám tay tốt.', 450000.00, 50, 'spigen-iphone-15.jpg'),
(10, 3, 'Sạc dự phòng Anker 20000mAh', 'sac-du-phong-anker-20000mah', 'Sạc dự phòng Anker PowerCore 20000mAh, công suất 20W, hỗ trợ sạc nhanh PD và QC 3.0, 2 cổng USB-A + 1 cổng USB-C, an toàn đa lớp.', 890000.00, 40, 'anker-20000mah.jpg'),
(11, 3, 'Cáp sạc Type-C to Lightning', 'cap-sac-type-c-to-lightning', 'Cáp sạc Type-C to Lightning chính hãng Apple, dài 1m, hỗ trợ sạc nhanh PD 20W, đồng bộ dữ liệu tốc độ cao, tương thích iPhone/iPad.', 550000.00, 60, 'cable-type-c-lightning.jpg'),
(12, 3, 'Kính cường lực iPhone 15 Pro Max', 'kinh-cuong-luc-iphone-15-pro-max', 'Kính cường lực 9H cho iPhone 15 Pro Max, độ trong suốt cao, oleophobic chống bám vân tay, độ cứng cao chống va đập, dễ dàng lắp đặt.', 150000.00, 80, 'tempered-glass-iphone-15.jpg');

-- Thêm dữ liệu mẫu: Users (3 tài khoản)
-- Mật khẩu mặc định cho tất cả: 123456
INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `phone`, `address`, `role`) VALUES
(1, 'admin@tranvanmy.com', '123456', 'Trần Văn Mỹ', '0901234567', '123 Đường Trần Đại Nghĩa, Q.Hai Bà Trưng, Hà Nội', 'admin'),
(2, 'user1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn An', '0912345678', '456 Đường Giải Phóng, Q.Hoàng Mai, Hà Nội', 'customer'),
(3, 'user2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Bình', '0923456789', '789 Đường Nguyễn Trãi, Q.Thanh Xuân, Hà Nội', 'customer');

-- Thêm dữ liệu mẫu: Orders (5 đơn hàng ở 4 trạng thái khác nhau)
INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `customer_name`, `customer_phone`, `customer_address`, `note`, `created_at`) VALUES
(1, 2, 30440000.00, 'completed', 'Nguyễn Văn An', '0912345678', '456 Đường Giải Phóng, Q.Hoàng Mai, Hà Nội', 'Giao giờ hành chính', '2026-11-01 10:30:00'),
(2, 3, 6440000.00, 'shipping', 'Trần Thị Bình', '0923456789', '789 Đường Nguyễn Trãi, Q.Thanh Xuân, Hà Nội', NULL, '2026-11-03 14:20:00'),
(3, 2, 19540000.00, 'pending', 'Nguyễn Văn An', '0912345678', '456 Đường Giải Phóng, Q.Hoàng Mai, Hà Nội', 'Gọi trước khi giao', '2026-11-05 09:15:00'),
(4, 3, 8990000.00, 'completed', 'Trần Thị Bình', '0923456789', '789 Đường Nguyễn Trãi, Q.Thanh Xuân, Hà Nội', NULL, '2026-10-28 16:45:00'),
(5, 2, 3990000.00, 'cancelled', 'Nguyễn Văn An', '0912345678', '456 Đường Giải Phóng, Q.Hoàng Mai, Hà Nội', 'Đổi ý không mua nữa', '2026-11-04 11:00:00');

-- Thêm dữ liệu mẫu: Order Items
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `price`, `quantity`) VALUES
-- Đơn hàng 1: iPhone 15 Pro Max + AirPods Pro
(1, 1, 'iPhone 15 Pro Max 256GB', 29990000.00, 1),
(1, 5, 'AirPods Pro Gen 2', 5990000.00, 1),
(1, 9, 'Ốp lưng Spigen iPhone 15 Pro', 450000.00, 1),
-- Đơn hàng 2: AirPods Pro + Sạc dự phòng
(2, 5, 'AirPods Pro Gen 2', 5990000.00, 1),
(2, 10, 'Sạc dự phòng Anker 20000mAh', 890000.00, 1),
-- Đơn hàng 3: Xiaomi 14 Pro + Tai nghe
(3, 3, 'Xiaomi 14 Pro 512GB', 18990000.00, 1),
(3, 11, 'Cáp sạc Type-C to Lightning', 550000.00, 1),
-- Đơn hàng 4: Sạc dự phòng + Sony tai nghe
(4, 6, 'Sony WH-1000XM5', 7990000.00, 1),
(4, 10, 'Sạc dự phòng Anker 20000mAh', 890000.00, 1),
(4, 9, 'Ốp lưng Spigen iPhone 15 Pro', 450000.00, 1),
-- Đơn hàng 5 (đã hủy): Galaxy Buds2 Pro
(5, 7, 'Samsung Galaxy Buds2 Pro', 3990000.00, 1);

COMMIT;