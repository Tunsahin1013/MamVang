-- ==========================================================
-- CANTEEN MANAGEMENT SYSTEM - INITIAL SEED DATA
-- Database: canteen_management
-- All passwords:
-- Admin: Admin@123
-- Employee: Employee@123
-- Customer: Customer@123
-- ==========================================================

USE `canteen_management`;

-- Disable foreign key checks for clean seed
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `chat_messages`;
TRUNCATE TABLE `chats`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `wallet_transactions`;
TRUNCATE TABLE `wallets`;
TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `vouchers`;
TRUNCATE TABLE `attendance`;
TRUNCATE TABLE `employee_shifts`;
TRUNCATE TABLE `shifts`;
TRUNCATE TABLE `purchase_order_items`;
TRUNCATE TABLE `purchase_orders`;
TRUNCATE TABLE `inventory_transactions`;
TRUNCATE TABLE `suppliers`;
TRUNCATE TABLE `payments`;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `employees`;
TRUNCATE TABLE `customers`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. SEED USERS
-- Note: bcrypt hash for Admin@123: $2y$10$e0MYzXyjpJS7Pd0RVvHwHeFv3d.rA18fT1kOaTqLhJ34wM7/4gWti
-- bcrypt hash for Employee@123: $2y$10$e0MYzXyjpJS7Pd0RVvHwHeFv3d.rA18fT1kOaTqLhJ34wM7/4gWti
-- bcrypt hash for Customer@123: $2y$10$e0MYzXyjpJS7Pd0RVvHwHeFv3d.rA18fT1kOaTqLhJ34wM7/4gWti
-- (We use standard bcrypt hashes that seed.php also recalculates on setup)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `status`, `created_at`) VALUES
(1, 'Nguyễn Quản Trị (Admin)', 'admin@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(2, 'Trần Nhân Viên (Employee)', 'employee@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EMPLOYEE', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(3, 'Lê Bếp Trưởng (Chef)', 'chef@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EMPLOYEE', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(4, 'Phạm Thu Ngân (Cashier)', 'cashier@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EMPLOYEE', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(5, 'Hoàng Sinh Viên (Customer)', 'customer@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(6, 'Vũ Sinh Viên', 'sinhvien1@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(7, 'Đỗ Giảng Viên', 'giangvien@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(8, 'Bùi Văn Nam', 'nam.bui@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(9, 'Ngô Thu Trang', 'trang.ngo@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW()),
(10, 'Đinh Minh Khang', 'khang.dinh@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=200&q=80', 'ACTIVE', NOW());

-- 2. SEED CUSTOMERS
INSERT INTO `customers` (`id`, `user_id`, `phone`, `address`, `points`, `created_at`) VALUES
(1, 5, '0912345678', 'Khu B - Ký túc xá Đại học', 280, NOW()),
(2, 6, '0987654321', 'Tòa H1, Phòng 402', 150, NOW()),
(3, 7, '0905112233', 'Khoa Công Nghệ Thông Tin', 520, NOW()),
(4, 8, '0933445566', 'Khu A - Ký túc xá', 80, NOW()),
(5, 9, '0977889900', 'Tòa C2, Phòng 205', 310, NOW()),
(6, 10, '0966554433', 'Tòa H2, Phòng 510', 95, NOW());

-- 3. SEED EMPLOYEES
INSERT INTO `employees` (`id`, `user_id`, `phone`, `department`, `position`, `salary`, `hire_date`, `status`, `created_at`) VALUES
(1, 2, '0988112233', 'Phục Vụ & Pha Chế', 'Trưởng Ca Phục Vụ', 8500000.00, '2025-01-15', 'ACTIVE', NOW()),
(2, 3, '0988445566', 'Bếp Căn Tin', 'Bếp Trưởng', 12000000.00, '2024-09-01', 'ACTIVE', NOW()),
(3, 4, '0988778899', 'Thu Ngân & Quầy POS', 'Nhân Viên Thu Ngân', 7500000.00, '2025-02-10', 'ACTIVE', NOW());

-- 4. SEED CATEGORIES
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`) VALUES
(1, 'Cơm Trưa & Món Mặn', 'com-trua-mon-man', 'Các món cơm phần, cơm đĩa nóng hổi bổ dưỡng cho bữa ăn chính.', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80', 'ACTIVE'),
(2, 'Món Nước & Bún Phở', 'mon-nuoc-bun-pho', 'Phở bò, bún bò, hủ tiếu với nước dùng thơm ngọt từ xương hầm.', 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=500&q=80', 'ACTIVE'),
(3, 'Bánh Mì & Đồ Ăn Sáng', 'banh-mi-do-an-sang', 'Bánh mì giòn rụm, xôi mặn, sandwich tiện lợi cho buổi sáng.', 'https://images.unsplash.com/photo-1621852004158-f3bc188ace2d?auto=format&fit=crop&w=500&q=80', 'ACTIVE'),
(4, 'Đồ Uống & Trà Sữa', 'do-uong-tra-sua', 'Trà đào, trà vải, trà sữa trân châu và nước mát thanh nhiệt.', 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=500&q=80', 'ACTIVE'),
(5, 'Cà Phê & Nước Ép', 'ca-phe-nuoc-ep', 'Cà phê nguyên chất rang xay, nước ép trái cây tươi mỗi ngày.', 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=500&q=80', 'ACTIVE'),
(6, 'Tráng Miệng & Ăn Vặt', 'trang-mieng-an-vat', 'Chè, sữa chua dẻo, nem chua rán, khoai tây chiên giòn.', 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=500&q=80', 'ACTIVE');

-- 5. SEED PRODUCTS
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `cost_price`, `stock_quantity`, `image`, `is_featured`, `status`) VALUES
(1, 1, 'Cơm Tấm Sườn Bì Chả Đặc Biệt', 'com-tam-suon-bi-cha', 'Sườn cốt lết nướng mật ong thơm lừng kèm bì thính, chả trứng hấp béo ngậy và nước mắm chua ngọt đặc trưng.', 45000.00, 25000.00, 50, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(2, 1, 'Cơm Gà Xối Mỡ Da Giòn', 'com-ga-xoi-mo-da-gion', 'Đùi gà góc tư chiên xối mỡ vàng ươm da giòn rụm, cơm rang hạt điều thơm lừng kèm dưa góp.', 48000.00, 27000.00, 45, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(3, 1, 'Cơm Bò Xào Hành Tây', 'com-bo-xao-hanh-tay', 'Thịt bò tươi xào lăn hành tây cần tây sốt dầu hào thơm phức, cơm dẻo nóng hổi kèm canh chua.', 42000.00, 24000.00, 40, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(4, 1, 'Cơm Cá Kho Tộ Miền Tây', 'com-ca-kho-to', 'Cá quả kho tộ đậm đà sốt tiêu ớt cay the hấp dẫn, ăn cùng rau luộc kho quẹt đưa cơm.', 40000.00, 21000.00, 35, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(5, 1, 'Cơm Sườn Non Rim Chua Ngọt', 'com-suon-non-rim', 'Sườn non chặt khúc đảo cháy cạnh rim nước sốt dấm đường cà chua sánh mịn.', 42000.00, 23000.00, 30, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),

(6, 2, 'Phở Bò Tái Nạm Hà Nội', 'pho-bo-tai-nam', 'Nước dùng ninh từ xương ống bò 12 tiếng thơm hương hoa hồi quế, thịt bò mềm ngọt kèm bánh phở tươi.', 45000.00, 25000.00, 60, 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(7, 2, 'Bún Bò Huế Chả Cua Thịt Nạm', 'bun-bo-hue-cha-cua', 'Vị cay nồng của sả ớt hòa quyện mắm ruốc đặc trưng xứ Huế, sợi bún to ăn kèm chả cua giòn ngọt.', 45000.00, 26000.00, 50, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(8, 2, 'Hủ Tiếu Nam Vang Thập Cẩm', 'hu-tieu-nam-vang', 'Hủ tiếu tôm tươi, thịt băm, gan heo trứng cút nước dùng trong thanh ngọt từ củ cải quả lê.', 40000.00, 22000.00, 40, 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(9, 2, 'Mì Quảng Gà Trứng Cút', 'mi-quang-ga', 'Sợi mì vàng óng nghệ, gà ta chặt miếng rim ngấm vị, ăn kèm bánh tráng nướng giòn rụm và đậu phộng.', 38000.00, 20000.00, 35, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),

(10, 3, 'Bánh Mì Thịt Nướng Sốt Đặc Biệt', 'banh-mi-thit-nuong', 'Bánh mì nóng giòn kẹp thịt nướng sả thơm phức, pate béo ngậy, đồ chua tươi và rau mùi chan sốt đậm đà.', 25000.00, 12000.00, 80, 'https://images.unsplash.com/photo-1621852004158-f3bc188ace2d?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(11, 3, 'Bánh Mì Chả Lụa Trứng Ốp La', 'banh-mi-cha-lua-trung', 'Bánh mì kẹp 2 trứng gà ốp la lòng đào béo ngậy, chả lụa hảo hạng và tiêu đen xay.', 22000.00, 10000.00, 70, 'https://images.unsplash.com/photo-1509722747041-616f39b57569?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(12, 3, 'Xôi Mặn Thập Cẩm Chà Bông Lạp Xưởng', 'xoi-man-thap-cam', 'Nếp cái hoa vàng dẻo thơm, rưới mỡ hành, lạp xưởng nướng thái lát, chà bông và ruốc tôm tép giòn cay.', 25000.00, 11000.00, 40, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),

(13, 4, 'Trà Đào Cam Sả Tươi Mát', 'tra-dao-cam-sa', 'Trà đen ủ lạnh pha nước cốt cam vàng mọng nước, hương sả tươi dịu thơm và miếng đào giòn ngâm.', 28000.00, 11000.00, 90, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(14, 4, 'Trà Sữa Trân Châu Đường Đen', 'tra-sua-tran-chau-duong-den', 'Sữa tươi thanh trùng Đà Lạt kết hợp trân châu thủ công nấu đường đen dẻo dai béo ngậy.', 32000.00, 14000.00, 85, 'https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(15, 4, 'Trà Vải Hoa Hồng Thanh Nhiệt', 'tra-vai-hoa-hong', 'Trà lài ướp hương hoa hồng tự nhiên, quả vải thiều mọng nước giải khát tuyệt đỉnh.', 30000.00, 12000.00, 60, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(16, 4, 'Trà Xanh Thái Đỏ Trân Châu Trắng', 'tra-thai-do', 'Hương vị trà sữa Thái Lan truyền thống thơm nồng kèm trân châu trắng giòn sần sật.', 26000.00, 10000.00, 70, 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),

(17, 5, 'Cà Phê Sữa Đá Sài Gòn', 'ca-phe-sua-da', 'Cà phê Robusta Đắk Lắk nguyên chất pha phin truyền thống hòa quyện sữa đặc thơm ngọt đậm đà.', 20000.00, 7000.00, 100, 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(18, 5, 'Cà Phê Đen Đá Nguyên Chất', 'ca-phe-den-da', 'Hạt cà phê rang mộc thơm nồng quyến rũ, vị đắng dịu êm đánh thức tỉnh táo.', 18000.00, 6000.00, 100, 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(19, 5, 'Bạc Xỉu 3 Tầng Kem Béo', 'bac-xiu-ba-tang', 'Tầng sữa tươi béo ngậy, sữa đặc ngọt dịu và lớp bọt cà phê thơm lừng.', 24000.00, 9000.00, 60, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(20, 5, 'Nước Ép Dưa Hấu Bạc Hà', 'nuoc-ep-dua-hau', 'Dưa hấu tươi ép 100% nguyên chất không đường thêm chút lá bạc hà mát lạnh.', 28000.00, 11000.00, 45, 'https://images.unsplash.com/photo-1589733955941-5eeaf752f6dd?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),

(21, 6, 'Khoai Tây Chiên Lắc Phô Mai', 'khoai-tay-chien-lac-pho-mai', 'Khoai tây cọng Bỉ chiên vàng giòn rụm lắc bột phô mai mặn béo cay nhẹ.', 25000.00, 11000.00, 60, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=600&q=80', 1, 'AVAILABLE'),
(22, 6, 'Nem Chua Rán Hà Nội Giòn Cay', 'nem-chua-ran', 'Nem chua lăn bột chiên xù nóng giòn chấm tương ớt cay nồng đậm vị.', 30000.00, 14000.00, 50, 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(23, 6, 'Chè Khúc Bạch Hạnh Nhân Thanh Mát', 'che-khuc-bach', 'Khúc bạch phô mai sữa dẻo mềm tan trong miệng, hạt hạnh nhân rang bùi, trái nhãn ngọt lịm.', 28000.00, 12000.00, 35, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE'),
(24, 6, 'Sữa Chua Trái Cây Hạt Chia', 'sua-chua-trai-cay', 'Sữa chua lên men tự nhiên kết hợp kiwi, dâu tây, xoài và hạt chia organic bổ dưỡng.', 25000.00, 10000.00, 40, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80', 0, 'AVAILABLE');

-- 6. SEED SUPPLIERS
INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `status`) VALUES
(1, 'Công Ty Nông Sản Sạch Đà Lạt', 'Phan Văn Hưng', 'contact@dalatfarm.vn', '0901234567', '45 Trần Phú, TP. Đà Lạt', 'ACTIVE'),
(2, 'Công Ty TNHH Thực Phẩm Tươi Sống Vissan', 'Nguyễn Thị Loan', 'sales@vissanfood.com', '0902345678', '420 Nơ Trang Long, Bình Thạnh, TP.HCM', 'ACTIVE'),
(3, 'Đại Lý Gạo Sạch Miền Tây ST25', 'Trần Đình Trọng', 'gaost25@mientay.vn', '0903456789', '12 QL1A, TP. Cần Thơ', 'ACTIVE'),
(4, 'Nhà Phân Phối Cà Phê & Đồ Uống Tân Hiệp', 'Lê Hữu Phúc', 'tanhiep@beverages.vn', '0904567890', '88 Quang Trung, Gò Vấp, TP.HCM', 'ACTIVE'),
(5, 'Công Ty Gia Vị & Nông Dược Hải Châu', 'Đỗ Thanh Hương', 'haichau@spices.vn', '0905678901', '15 Ngọc Hồi, Hoàng Mai, Hà Nội', 'ACTIVE');

-- 7. SEED SHIFTS
INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `description`) VALUES
(1, 'Ca Sáng (Chuẩn Bị & Phục Vụ Ăn Sáng)', '06:00:00', '11:30:00', 'Chuẩn bị bếp sáng, bánh mì, bún phở và phục vụ điểm tâm.'),
(2, 'Ca Trưa (Cao Điểm Cơm Trưa Căn Tin)', '11:00:00', '16:30:00', 'Phục vụ cơm trưa cao điểm cho Sinh Viên và cán bộ giảng viên.'),
(3, 'Ca Chiều Tối (Ăn Nhẹ & Dọn Dẹp Bếp)', '16:00:00', '21:00:00', 'Phục vụ đồ ăn tối, nước uống giải khát và tổng vệ sinh cuối ngày.');

-- 8. SEED EMPLOYEE SHIFTS
INSERT INTO `employee_shifts` (`id`, `employee_id`, `shift_id`, `shift_date`, `status`) VALUES
(1, 1, 1, CURDATE(), 'ATTENDED'),
(2, 2, 2, CURDATE(), 'SCHEDULED'),
(3, 3, 2, CURDATE(), 'SCHEDULED'),
(4, 1, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'SCHEDULED'),
(5, 2, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'SCHEDULED');

-- 9. SEED ATTENDANCE
INSERT INTO `attendance` (`id`, `employee_id`, `check_in_time`, `check_out_time`, `total_hours`, `status`, `notes`) VALUES
(1, 1, CONCAT(CURDATE(), ' 05:55:00'), CONCAT(CURDATE(), ' 11:35:00'), 5.67, 'PRESENT', 'Đúng giờ, bàn giao ca tốt.'),
(2, 2, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 10:50:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 16:30:00'), 5.67, 'PRESENT', 'Ca trưa đông khách, hoàn thành tốt.'),
(3, 3, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 11:05:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 16:35:00'), 5.50, 'LATE', 'Trễ 5 phút do kẹt xe.');

-- 10. SEED VOUCHERS
INSERT INTO `vouchers` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `usage_limit`, `times_used`, `start_date`, `end_date`, `status`) VALUES
(1, 'CHAOBANMOI', 'Giảm 20% cho đơn hàng đầu tiên của tân Sinh Viên và Sinh Viên mới', 'PERCENTAGE', 20.00, 30000.00, 20000.00, 500, 42, '2026-01-01', '2026-12-31', 'ACTIVE'),
(2, 'CANTEEN50', 'Giảm trực tiếp 50.000đ cho đơn hàng tiệc hoặc nhóm trên 200.000đ', 'FIXED', 50000.00, 200000.00, 50000.00, 100, 18, '2026-01-01', '2026-12-31', 'ACTIVE'),
(3, 'TRUAVUIVE', 'Giảm 10% cho toàn bộ thực đơn cơm trưa trong tuần', 'PERCENTAGE', 10.00, 40000.00, 15000.00, 300, 85, '2026-01-01', '2026-12-31', 'ACTIVE'),
(4, 'FREESHIP', 'Giảm 15.000đ phí giao đồ ăn tận phòng ký túc xá', 'FIXED', 15000.00, 50000.00, 15000.00, 200, 37, '2026-01-01', '2026-12-31', 'ACTIVE'),
(5, 'VIPCOMBO', 'Giảm 15% cho combo đồ ăn kèm đồ uống bất kỳ', 'PERCENTAGE', 15.00, 60000.00, 25000.00, 150, 12, '2026-01-01', '2026-12-31', 'ACTIVE');

-- 11. SEED WALLETS
INSERT INTO `wallets` (`id`, `user_id`, `balance`) VALUES
(1, 5, 450000.00),
(2, 6, 125000.00),
(3, 7, 850000.00),
(4, 8, 30000.00),
(5, 9, 210000.00),
(6, 10, 95000.00);

-- 12. SEED WALLET TRANSACTIONS
INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `user_id`, `type`, `amount`, `balance_after`, `description`, `reference_id`) VALUES
(1, 1, 5, 'DEPOSIT', 500000.00, 500000.00, 'Nạp tiền vào ví căn tin qua chuyển khoản VietQR', 'VNPAY_TXN_001'),
(2, 1, 5, 'PAYMENT', 50000.00, 450000.00, 'Thanh toán đơn hàng #CT-1001 tại căn tin', 'ORD_CT_1001');

-- 13. SEED ORDERS
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_id`, `total_amount`, `discount_amount`, `final_amount`, `voucher_code`, `payment_method`, `payment_status`, `order_status`, `table_number`, `notes`, `created_at`) VALUES
(1, 'CT-260901', 5, 1, 73000.00, 14600.00, 58400.00, 'CHAOBANMOI', 'WALLET', 'PAID', 'COMPLETED', 'Bàn 08', 'Không hành lá, nhiều ớt rim.', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 'CT-260902', 6, 2, 45000.00, 0.00, 45000.00, NULL, 'CASH', 'PAID', 'READY', 'Bàn 03', 'Mang đi hộp giấy.', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
(3, 'CT-260903', 7, 3, 104000.00, 10400.00, 93600.00, 'TRUAVUIVE', 'BANK_TRANSFER', 'PAID', 'PREPARING', 'Bàn 12', 'Giao lúc 11h45.', DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
(4, 'CT-260904', 8, 4, 38000.00, 0.00, 38000.00, NULL, 'CASH', 'PENDING', 'CONFIRMED', 'Bàn 05', 'Ăn tại chỗ.', DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
(5, 'CT-260905', 9, 5, 50000.00, 0.00, 50000.00, NULL, 'WALLET', 'PAID', 'PENDING', 'Bàn 15', 'Nhiều đá cho đồ uống.', DATE_SUB(NOW(), INTERVAL 2 MINUTE));

-- 14. SEED ORDER ITEMS
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`, `notes`) VALUES
(1, 1, 1, 'Cơm Tấm Sườn Bì Chả Đặc Biệt', 45000.00, 1, 45000.00, 'Không hành mỡ'),
(2, 1, 13, 'Trà Đào Cam Sả Tươi Mát', 28000.00, 1, 28000.00, 'Ít ngọt 50% đường'),
(3, 2, 6, 'Phở Bò Tái Nạm Hà Nội', 45000.00, 1, 45000.00, 'Bò tái mềm'),
(4, 3, 2, 'Cơm Gà Xối Mỡ Da Giòn', 48000.00, 1, 48000.00, 'Da giòn rụm'),
(5, 3, 14, 'Trà Sữa Trân Châu Đường Đen', 32000.00, 1, 32000.00, '70% đá'),
(6, 3, 24, 'Sữa Chua Trái Cây Hạt Chia', 24000.00, 1, 24000.00, 'Tươi mát'),
(7, 4, 9, 'Mì Quảng Gà Trứng Cút', 38000.00, 1, 38000.00, 'Bánh tráng giòn'),
(8, 5, 21, 'Khoai Tây Chiên Lắc Phô Mai', 25000.00, 2, 50000.00, 'Nóng giòn');

-- 15. SEED PAYMENTS
INSERT INTO `payments` (`id`, `order_id`, `user_id`, `amount`, `payment_method`, `transaction_id`, `status`) VALUES
(1, 1, 5, 58400.00, 'WALLET', 'WAL_PAY_CT260901', 'COMPLETED'),
(2, 2, 6, 45000.00, 'CASH', 'CASH_REG_02', 'COMPLETED'),
(3, 3, 7, 93600.00, 'BANK_TRANSFER', 'MBBANK_QR_99214', 'COMPLETED');

-- 16. SEED REVIEWS
INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `comment`, `status`) VALUES
(1, 1, 5, 1, 5, 'Sườn nướng cực kỳ mềm và ngấm gia vị, chả trứng béo thơm, nước mắm pha xuất sắc. Sẽ tiếp tục ủng hộ căn tin!', 'APPROVED'),
(2, 13, 5, 1, 5, 'Trà đào rất thơm hương sả và cam tươi, miếng đào giòn ngọt không bị mềm nát. 10/10.', 'APPROVED'),
(3, 6, 6, 2, 5, 'Nước phở ngọt thanh chuẩn vị Hà Nội, thịt bò tái tươi mềm, bánh phở mỏng vừa ăn.', 'APPROVED'),
(4, 2, 7, 3, 4, 'Cơm gà da rất giòn, hạt cơm tơi xốp, chỉ mong căn tin cho thêm ít dưa chua nữa là tuyệt hảo.', 'APPROVED');

-- 17. SEED NOTIFICATIONS
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`) VALUES
(1, 5, 'Đơn hàng hoàn tất', 'Đơn hàng #CT-260901 của bạn đã được hoàn tất. Chúc bạn ngon miệng!', 'ORDER', 1, '/canteen-management/frontend/customer/orders.html'),
(2, 5, 'Voucher ưu đãi mới', 'Nhận ngay voucher CANTEEN50 giảm 50.000đ cho đơn hàng nhóm tại căn tin.', 'PROMO', 0, '/canteen-management/frontend/customer/vouchers.html'),
(3, 6, 'Món ăn đã sẵn sàng!', 'Đơn hàng #CT-260902 của bạn đã được chuẩn bị xong, mời bạn đến Quầy 01 nhận món.', 'ORDER', 0, '/canteen-management/frontend/customer/orders.html'),
(4, 1, 'Đơn hàng mới', 'Có đơn hàng mới #CT-260905 cần thu ngân xác nhận.', 'SYSTEM', 0, '/canteen-management/frontend/admin/orders.html');

-- 18. SEED CHATS
INSERT INTO `chats` (`id`, `customer_id`, `employee_id`, `last_message`, `updated_at`) VALUES
(1, 5, 2, 'Dạ em cảm ơn căn tin, cơm tấm trưa nay rất ngon ạ!', NOW());

-- 19. SEED CHAT MESSAGES
INSERT INTO `chat_messages` (`id`, `chat_id`, `sender_id`, `sender_role`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 5, 'CUSTOMER', 'Chào căn tin ạ, cho mình hỏi hôm nay cơm gà có món canh chua không?', 1, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 1, 2, 'EMPLOYEE', 'Dạ chào bạn! Hôm nay căn tin có canh chua cá bớp và canh bí đỏ sườn non ạ.', 1, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 1, 5, 'CUSTOMER', 'Dạ em cảm ơn căn tin, cơm tấm trưa nay rất ngon ạ!', 1, DATE_SUB(NOW(), INTERVAL 1 HOUR));
