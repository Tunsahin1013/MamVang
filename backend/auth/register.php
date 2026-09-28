<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CUSTOMER REGISTRATION API
 * Endpoint: POST /backend/auth/register.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Phương thức yêu cầu không hợp lệ. Chỉ chấp nhận POST.', null, 405);
}

$input = getJsonInput();
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($password)) {
    sendResponse(false, 'Họ tên, email và mật khẩu là bắt buộc.', null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Định dạng email không hợp lệ.', null, 400);
}

if (strlen($password) < 6) {
    sendResponse(false, 'Mật khẩu phải có độ dài tối thiểu 6 ký tự.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Check if email already exists
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetch()) {
        sendResponse(false, 'Email này đã được sử dụng. Vui lòng chọn email khác hoặc đăng nhập.', null, 409);
    }

    // Begin Transaction
    $pdo->beginTransaction();

    // Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $defaultAvatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80';

    // Insert User
    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password, role, avatar, status) VALUES (?, ?, ?, 'CUSTOMER', ?, 'ACTIVE')");
    $stmtUser->execute([$name, $email, $hashedPassword, $defaultAvatar]);
    $userId = (int)$pdo->lastInsertId();

    // Insert Customer Profile
    $stmtCust = $pdo->prepare("INSERT INTO customers (user_id, phone, address, points) VALUES (?, ?, ?, 50)");
    $stmtCust->execute([$userId, $phone, $address]);

    // Initialize Customer Wallet with 0đ
    $stmtWal = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)");
    $stmtWal->execute([$userId]);

    // Send Welcome Notification
    $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, link) VALUES (?, ?, ?, 'PROMO', 0, ?)");
    $stmtNotif->execute([
        $userId,
        'Chào mừng bạn đến với Canteen!',
        'Bạn đã được tặng 50 điểm tích lũy khởi đầu và mã voucher CHAOBANMOI giảm 20% cho đơn hàng đầu tiên!',
        '/canteen-management/frontend/customer/vouchers.html'
    ]);

    $pdo->commit();

    // Generate JWT Token
    $token = JWT::encode([
        'user_id' => $userId,
        'email' => $email,
        'name' => $name,
        'role' => 'CUSTOMER'
    ], 86400 * 7);

    sendResponse(true, 'Đăng ký tài khoản thành công! Chào mừng bạn đến với Căn Tin.', [
        'token' => $token,
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => 'CUSTOMER',
            'avatar' => $defaultAvatar,
            'points' => 50,
            'wallet_balance' => 0.00
        ],
        'redirect_url' => '/canteen-management/frontend/customer/home.html'
    ], 201);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Đăng ký thất bại: ' . $e->getMessage(), null, 500);
}
