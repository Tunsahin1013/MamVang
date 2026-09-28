# TÀI LIỆU REST API - CANTEEN MANAGEMENT SYSTEM
**Chuẩn công nghệ:** PHP 8.x Thuần, MySQL PDO (`canteen_management`), Xác thực JWT (HS256)

---

## 1. TỔNG QUAN XÁC THỰC & MÃ TRẠNG THÁI
Mọi API bảo vệ (Protected) yêu cầu gửi kèm HTTP Header:
```http
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json
```

### Các Mã Trạng Thái HTTP:
- `200 OK`: Xử lý thành công.
- `201 Created`: Tạo mới tài nguyên thành công.
- `400 Bad Request`: Thiếu tham số hoặc dữ liệu không hợp lệ.
- `401 Unauthorized`: Chưa cung cấp Token hoặc Token hết hạn/sai chữ ký.
- `403 Forbidden`: Người dùng không đủ quyền thực hiện hành động này.
- `404 Not Found`: Không tìm thấy bản ghi.
- `409 Conflict`: Trùng lặp dữ liệu duy nhất (email, mã code, slug).
- `500 Internal Server Error`: Lỗi máy chủ hoặc giao dịch cơ sở dữ liệu.

---

## 2. MODULE: AUTHENTICATION (`/backend/auth/`)

### 2.1 Đăng Nhập
- **Endpoint:** `POST /backend/auth/login.php`
- **Quyền:** Public
- **Request Body:**
```json
{
  "email": "admin@canteen.com",
  "password": "Admin@123"
}
```
- **Response (200 OK):**
```json
{
  "success": true,
  "message": "Đăng nhập thành công!",
  "data": {
    "token": "eyJhbGciOi...",
    "user": {
      "id": 1,
      "name": "Nguyễn Quản Trị (Admin)",
      "email": "admin@canteen.com",
      "role": "ADMIN",
      "avatar": "https://...",
      "status": "ACTIVE"
    },
    "redirect_url": "/canteen-management/frontend/admin/dashboard.html"
  }
}
```

### 2.2 Đăng Ký Sinh Viên
- **Endpoint:** `POST /backend/auth/register.php`
- **Quyền:** Public
- **Request Body:**
```json
{
  "name": "Nguyễn Văn A",
  "email": "vana@canteen.com",
  "password": "Customer@123",
  "phone": "0912345678",
  "address": "KTX Khu B, Phòng 302"
}
```
- **Response (201 Created):** Trả về thông tin Sinh Viên, ví ban đầu (0đ), 50 điểm tích lũy khởi điểm và JWT token.

### 2.3 Lấy Thông Tin Người Dùng Hiện Tại
- **Endpoint:** `GET /backend/auth/me.php`
- **Quyền:** ADMIN, EMPLOYEE, CUSTOMER (JWT)

### 2.4 Đổi Mật Khẩu
- **Endpoint:** `POST /backend/auth/change_password.php`
- **Quyền:** Đã đăng nhập
- **Request Body:**
```json
{
  "current_password": "Customer@123",
  "new_password": "NewSecretPassword@123"
}
```

---

## 3. MODULE: MÓN ĂN & THỰC ĐƠN (`/backend/products/`)

### 3.1 Danh Sách Món Ăn & Tìm Kiếm
- **Endpoint:** `GET /backend/products/list.php`
- **Tham số URL:** `category_id`, `search`, `status` (AVAILABLE, ALL), `sort` (price_asc, price_desc, bestseller, new), `page`, `limit`
- **Response (200 OK):** Danh sách món ăn kèm lượt mua, số sao đánh giá trung bình.

### 3.2 Chi Tiết Món Ăn
- **Endpoint:** `GET /backend/products/get.php?id=1`
- **Response (200 OK):** Thông tin món ăn, hình ảnh, tồn kho và danh sách bình luận đã duyệt.

### 3.3 Tạo Món Ăn Mới
- **Endpoint:** `POST /backend/products/create.php`
- **Quyền:** ADMIN, EMPLOYEE
- **Request Body:**
```json
{
  "category_id": 1,
  "name": "Cơm Sườn Non Sốt Cay",
  "price": 45000,
  "cost_price": 25000,
  "stock_quantity": 50,
  "image": "https://...",
  "description": "Sườn non tươi ngon...",
  "is_featured": 1,
  "status": "AVAILABLE"
}
```

### 3.4 Cập Nhật Món Ăn
- **Endpoint:** `POST/PUT /backend/products/update.php`
- **Quyền:** ADMIN, EMPLOYEE

### 3.5 Xóa / Ngừng Kinh Doanh
- **Endpoint:** `POST/DELETE /backend/products/delete.php`
- **Quyền:** ADMIN

---

## 4. MODULE: ĐƠN HÀNG (`/backend/orders/`) - ACID TRANSACTION

### 4.1 Đặt Đơn Hàng Mới
- **Endpoint:** `POST /backend/orders/create.php`
- **Quyền:** Đã đăng nhập (CUSTOMER, EMPLOYEE, ADMIN)
- **Quy trình Transaction:**
  1. `BEGIN TRANSACTION`
  2. Khóa dòng kiểm tra tồn kho (`FOR UPDATE`)
  3. Lấy giá từ bảng `products` trong Database (không tin giá client)
  4. Tính `subtotal`
  5. Áp dụng mã `vouchers` (nếu có), kiểm tra điều kiện & trừ tiền
  6. Xử lý thanh toán: Nếu `WALLET`, trừ số dư ví người dùng, kiểm tra `balance >= final_amount`
  7. Tạo bản ghi `orders`
  8. Tạo các dòng `order_items`
  9. Giảm tồn kho món ăn `stock_quantity = stock_quantity - ?`
  10. Ghi nhật ký xuất bán `inventory_transactions`
  11. Cộng điểm tích lũy Sinh Viên (`customers.points`)
  12. Gửi thông báo đến tài khoản (`notifications`)
  13. `COMMIT TRANSACTION` (Nếu lỗi rollback 100%).
- **Request Body:**
```json
{
  "items": [
    { "product_id": 1, "quantity": 1, "notes": "Không cay" },
    { "product_id": 13, "quantity": 2, "notes": "Ít đá" }
  ],
  "table_number": "Bàn 08",
  "notes": "Lấy thêm muỗng đũa",
  "payment_method": "WALLET",
  "voucher_code": "CHAOBANMOI"
}
```

### 4.2 Lấy Danh Sách Đơn Hàng
- **Endpoint:** `GET /backend/orders/list.php`
- **Quyền:**
  - `CUSTOMER`: Chỉ thấy đơn của chính mình (`WHERE user_id = ?`)
  - `EMPLOYEE / ADMIN`: Thấy toàn bộ đơn hàng căn tin.

### 4.3 Cập Nhật Trạng Thái Đơn Hàng
- **Endpoint:** `POST /backend/orders/update_status.php`
- **Quyền:** ADMIN, EMPLOYEE
- **Request Body:**
```json
{
  "order_id": 1,
  "status": "PREPARING"
}
```
*Các trạng thái hợp lệ:* `PENDING`, `CONFIRMED`, `PREPARING`, `READY`, `COMPLETED`, `CANCELLED`.

### 4.4 Hủy Đơn Hàng
- **Endpoint:** `POST /backend/orders/cancel.php`
- **Quyền:** CUSTOMER (khi PENDING), EMPLOYEE, ADMIN
- **Logic:** Tự động hoàn lại số lượng tồn kho cho các món ăn và hoàn tiền lại ví nếu thanh toán bằng WALLET.

---

## 5. MODULE: TỒN KHO & NHÀ CUNG CẤP (`/backend/inventory/`)

### 5.1 Báo Cáo Tồn Kho & Cảnh Báo Sắp Hết
- **Endpoint:** `GET /backend/inventory/list.php`
- **Quyền:** ADMIN, EMPLOYEE
- **Cảnh báo:** `LOW_STOCK` khi tồn kho <= 10; `OUT_OF_STOCK` khi = 0.

### 5.2 Nhập Kho Thực Phẩm
- **Endpoint:** `POST /backend/inventory/import.php`
- **Quyền:** ADMIN, EMPLOYEE
- **Request Body:**
```json
{
  "product_id": 1,
  "quantity": 50,
  "cost_price": 25000,
  "reason": "Nhập hàng từ nhà cung cấp"
}
```

### 5.3 Xuất Kho / Hủy Thực Phẩm Hết Hạn
- **Endpoint:** `POST /backend/inventory/export.php`
- **Quyền:** ADMIN, EMPLOYEE

---

## 6. MODULE: CHẤM CÔNG & CA LÀM (`/backend/attendance/`, `/backend/shifts/`)

### 6.1 Check-in Vào Ca
- **Endpoint:** `POST /backend/attendance/check_in.php`
- **Quyền:** EMPLOYEE
- **Logic:** Tự động so sánh với giờ bắt đầu ca được phân công để ghi nhận `PRESENT` hoặc `LATE`.

### 6.2 Check-out Tan Ca
- **Endpoint:** `POST /backend/attendance/check_out.php`
- **Quyền:** EMPLOYEE
- **Logic:** Tính toán số giờ làm việc thực tế `total_hours`.

### 6.3 Phân Ca Nhân Viên
- **Endpoint:** `POST /backend/shifts/assign.php`
- **Quyền:** ADMIN

---

## 7. MODULE: VÍ ĐIỆN TỬ CĂN TIN (`/backend/wallet/`)

### 7.1 Lấy Số Dư & Lịch Sử Giao Dịch
- **Endpoint:** `GET /backend/wallet/balance.php`
- **Quyền:** Đã đăng nhập

### 7.2 Nạp Tiền Vào Ví
- **Endpoint:** `POST /backend/wallet/topup.php`
- **Quyền:** Đã đăng nhập
- **Request Body:**
```json
{
  "amount": 100000,
  "method": "VIETQR"
}
```

---

## 8. MODULE: BÁO CÁO DOANH THU & THỐNG KÊ (`/backend/reports/`)

- `GET /backend/reports/revenue.php?start_date=2026-09-01&end_date=2026-09-30`: Báo cáo doanh thu thuần, giảm giá, số lượng đơn theo ngày.
- `GET /backend/reports/orders.php`: Phân bổ đơn hàng theo trạng thái và khung giờ cao điểm trong ngày.
- `GET /backend/reports/products.php`: Top 10 món bán chạy nhất và món bán chậm cần thúc đẩy.
- `GET /backend/reports/inventory.php`: Tổng giá trị vốn kho hàng và định giá theo danh mục.
