# CANTEEN MANAGEMENT SYSTEM - Hệ thống Mâm Vàng TOÀN DIỆN

Hệ thống Mâm Vàng và đặt món trực tuyến được xây dựng hoàn chỉnh theo kiến trúc nguyên bản: **PHP 8.x Thuần + MySQL PDO + JWT + HTML5/CSS3/JavaScript ES6+**. Phục vụ 3 nhóm người dùng: **Quản trị viên (ADMIN)**, **Nhân viên căn tin (EMPLOYEE)**, và **Sinh Viên (CUSTOMER)**.

---

## 1. CÔNG NGHỆ BẮT BUỘC SỬ DỤNG
- **Backend:** PHP 8.x (PHP thuần, cấu trúc REST API theo từng module chức năng).
- **Database:** MySQL (Database duy nhất: `canteen_management`).
- **Database Driver:** `PDO MySQL` (`pdo_mysql`), Prepared Statements 100% chống SQL Injection.
- **Xác thực:** JWT (JSON Web Token HS256) + `password_hash()` & `password_verify()`.
- **Frontend:** HTML5, CSS3, JavaScript ES6+ thuần (Fetch API tập trung qua `frontend/js/api.js`). Không dùng framework.
- **Máy chủ Web:** Apache / XAMPP.
- **Quản lý CSDL:** phpMyAdmin hoặc MySQL CLI.

---

## 2. TÀI KHOẢN TRUY CẬP MẪU ĐÃ SEED SẴN

| Vai trò | Email đăng nhập | Mật khẩu mặc định | Giao diện tự động chuyển hướng |
| :--- | :--- | :--- | :--- |
| **👑 ADMIN** | `admin@canteen.com` | `Admin@123` | `/frontend/admin/dashboard.html` |
| **👨‍🍳 EMPLOYEE** | `employee@canteen.com` | `Employee@123` | `/frontend/employee/dashboard.html` |
| **👤 CUSTOMER** | `customer@canteen.com` | `Customer@123` | `/frontend/customer/home.html` |

---

## 3. CẤU TRÚC THƯ MỤC DỰ ÁN

```
canteen-management/
├── database/
│   ├── create_database.sql       # Khởi tạo database canteen_management
│   ├── create_tables.sql         # 22 bảng hoàn chỉnh, foreign key, index
│   ├── insert_data.sql           # Dữ liệu mẫu thực tế, món ăn, tài khoản bcrypt
│   └── seed.php                  # Script tự động khởi tạo CSDL qua CLI/Browser
│
├── backend/
│   ├── config/
│   │   ├── database.php          # Kết nối PDO MySQL chuẩn Singleton
│   │   ├── jwt.php               # Mã hóa và giải mã JWT HS256 thuần PHP
│   │   └── cors.php              # Cấu hình CORS & Helper phản hồi JSON đồng nhất
│   ├── middleware/
│   │   └── auth.php              # Xác thực Token Bearer & phân quyền theo vai trò
│   ├── auth/                     # login, register, me, change_password, forgot_password
│   ├── categories/               # CRUD danh mục món ăn
│   ├── products/                 # CRUD món ăn, tìm kiếm, lọc danh mục, giá, tồn kho
│   ├── orders/                   # Tạo đơn (ACID Transaction), danh sách, đổi trạng thái, hủy đơn
│   ├── inventory/                # Kiểm kê kho, nhập kho, xuất hủy, cảnh báo sắp hết
│   ├── suppliers/                # Quản lý danh bạ nhà cung cấp thực phẩm
│   ├── attendance/               # Điểm danh Check-in, Check-out, tính giờ làm việc
│   ├── shifts/                   # Khung giờ ca làm việc & phân ca nhân viên
│   ├── vouchers/                 # Quản lý mã giảm giá, kiểm tra điều kiện áp dụng
│   ├── reviews/                  # Đánh giá món ăn 1-5 sao từ Sinh Viên đã mua
│   ├── wallet/                   # Ví điện tử căn tin, nạp tiền giả lập, lịch sử giao dịch
│   ├── notifications/            # Thông báo trạng thái đơn hàng, ưu đãi
│   ├── chat/                     # Kênh chat trực tiếp giữa Sinh Viên và nhân viên
│   ├── reports/                  # Thống kê doanh thu, đơn hàng, món bán chạy, giá trị kho
│   └── dashboard/                # Dữ liệu tổng hợp KPI cho Admin Dashboard
│
├── frontend/
│   ├── index.html                # Trang chủ giới thiệu hệ thống & cổng chọn vai trò
│   ├── login.html                # Đăng nhập (có nút 1-click điền tài khoản mẫu)
│   ├── register.html             # Đăng ký Sinh Viên mới
│   ├── forgot-password.html      # Đặt lại mật khẩu
│   ├── 403.html                  # Trang thông báo bị từ chối quyền truy cập
│   ├── css/
│   │   └── style.css             # Giao diện hiện đại, Responsive, hỗ trợ Dark Mode
│   ├── js/
│   │   ├── api.js                # API Client trung tâm, xử lý Fetch, JWT, Cart, Toast
│   │   └── auth.js               # Route Guard, bảo vệ trang & thông tin người dùng
│   ├── customer/                 # home.html, products.html, product-detail.html, cart.html,
│   │                             # checkout.html, orders.html, order-detail.html, wallet.html,
│   │                             # vouchers.html, profile.html, notifications.html, chat.html
│   ├── employee/                 # dashboard.html, orders.html, products.html, inventory.html,
│   │                             # attendance.html, shifts.html, profile.html
│   └── admin/                    # dashboard.html (Chart.js), users.html, customers.html,
│                                 # employees.html, categories.html, products.html, orders.html,
│                                 # inventory.html, suppliers.html, shifts.html, attendance.html,
│                                 # vouchers.html, reviews.html, reports.html
│
├── API_DOCUMENTATION.md          # Tài liệu đặc tả tất cả các Endpoint REST API
└── README.md                     # Hướng dẫn cài đặt và vận hành hệ thống
```

---

## 4. HƯỚNG DẪN CÀI ĐẶT & CHẠY TRÊN XAMPP

### Bước 1: Khởi động XAMPP
1. Mở **XAMPP Control Panel**.
2. Nhấn **Start** ở cả 2 dịch vụ **Apache** và **MySQL**.

### Bước 2: Đặt mã nguồn vào thư mục `htdocs`
1. Copy toàn bộ thư mục dự án vào:
   ```text
   C:\xampp\htdocs\canteen-management
   ```
2. Đảm bảo cấu trúc đường dẫn như sau:
   - Backend: `http://localhost/canteen-management/backend/...`
   - Frontend: `http://localhost/canteen-management/frontend/...`

### Bước 3: Cấu hình và Import Database
Bạn có thể chọn 1 trong 2 cách sau:

#### Cách A (Khuyên dùng - Nhanh nhất qua trình duyệt):
Mở trình duyệt và truy cập URL tự động chạy script:
```url
http://localhost/canteen-management/database/seed.php
```
Script sẽ tự động:
1. Tạo database `canteen_management` nếu chưa có.
2. Tạo 22 bảng dữ liệu và thiết lập các khóa ngoại.
3. Sinh mã băm mật khẩu `password_hash()` tương thích 100% với PHP của bạn.
4. Trả về kết quả JSON thông báo thành công.

#### Cách B (Nhập thủ công qua phpMyAdmin):
1. Mở trình duyệt vào `http://localhost/phpmyadmin/`.
2. Tạo database mới tên là `canteen_management`, bảng mã `utf8mb4_unicode_ci`.
3. Nhấp vào tab **Import** (Nhập):
   - Chọn file `database/create_tables.sql` -> Nhấn **Go**.
   - Chọn tiếp file `database/insert_data.sql` -> Nhấn **Go**.

---

## 5. KIỂM THỬ HỆ THỐNG THEO TỪNG VAI TRÒ

Mở trình duyệt và truy cập:
```url
http://localhost/canteen-management/frontend/index.html
```

### 1. Trải nghiệm Sinh Viên (Customer):
- Đăng nhập với tài khoản: `customer@canteen.com` / `Customer@123`.
- Duyệt thực đơn tại **Trang Chủ** hoặc **Tất Cả Món Ăn**.
- Tìm kiếm món ăn bằng ô Search hoặc lọc theo danh mục cơm trưa, bún phở, đồ uống.
- Thêm món vào giỏ hàng, tùy chỉnh số lượng, ghi chú (ví dụ: *không cay*).
- Vào **Giỏ Hàng**, nhập mã voucher `CHAOBANMOI` để được giảm 20%.
- Nhấn **Tiến Hành Đặt Đơn**, chọn vị trí nhận (*Bàn 08*), chọn phương thức thanh toán (*Ví Căn Tin* hoặc *Tiền Mặt*).
- Xem đơn hàng xuất hiện trong **Lịch Sử Đơn Hàng** với trạng thái *PENDING*.

### 2. Trải nghiệm Nhân Viên (Employee POS / Bếp):
- Mở tab ẩn danh hoặc đăng xuất, đăng nhập với: `employee@canteen.com` / `Employee@123`.
- Bảng điều khiển hiển thị trạng thái ca làm: Nhấn **Check-in Vào Ca**.
- Vào mục **Quản Lý Đơn Hàng**: Đơn hàng vừa đặt xuất hiện ở đầu danh sách.
- Chuyển trạng thái đơn: *PENDING* → *CONFIRMED* → *PREPARING* (Bếp đang nấu) → *READY* (Sẵn sàng nhận).
- Vào **Kho Hàng**: Kiểm tra số lượng tồn kho đã tự động giảm theo logic đơn hàng, thử chức năng Nhập kho hoặc Xuất kho hủy hàng.

### 3. Trải nghiệm Quản Trị Viên (Admin):
- Đăng nhập với tài khoản: `admin@canteen.com` / `Admin@123`.
- Xem **Dashboard** với biểu đồ Chart.js thống kê doanh thu theo ngày và tỷ lệ đơn hàng.
- Quản lý đầy đủ danh mục, sản phẩm, nhân viên, Sinh Viên, voucher, ca làm và chấm công.
- Truy cập mục **Báo Cáo Doanh Thu** để xem phân tích số liệu tài chính chi tiết.

---

## 6. XỬ LÝ SỰ CỐ THƯỜNG GẶP (TROUBLESHOOTING)
1. **Lỗi kết nối CSDL ("Lỗi kết nối cơ sở dữ liệu MySQL"):**
   - Mở file `backend/config/database.php`, kiểm tra xem cổng MySQL của XAMPP là `3306` hay cổng khác (ví dụ `3307`).
   - Kiểm tra thông tin `root` và mật khẩu (thông thường XAMPP mặc định mật khẩu là rỗng `""`).
2. **Lỗi 404 khi gọi API:**
   - Đảm bảo thư mục đặt đúng tại `htdocs/canteen-management`.
   - Nếu bạn đổi tên thư mục gốc, hãy mở file `frontend/js/api.js` và cập nhật hằng số `API_BASE` cho phù hợp.
