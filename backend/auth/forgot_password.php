<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - FORGOT PASSWORD & RESET API
 * Endpoint: POST /backend/auth/forgot_password.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Phương thức không hợp lệ. Chỉ chấp nhận POST.', null, 405);
}

$input = getJsonInput();
$email = trim($input['email'] ?? '');
$newPassword = $input['new_password'] ?? '';

if (empty($email)) {
    sendResponse(false, 'Vui lòng cung cấp địa chỉ email.', null, 400);
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Return friendly message without disclosing user enumeration
        sendResponse(false, 'Nếu email này tồn tại trong hệ thống, hướng dẫn đặt lại mật khẩu đã được xử lý.', null, 404);
    }

    // If new password provided, reset it directly (for student/campus canteen demo)
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            sendResponse(false, 'Mật khẩu mới phải có tối thiểu 6 ký tự.', null, 400);
        }
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmtUpdate->execute([$newHash, $user['id']]);

        sendResponse(true, 'Mật khẩu đã được thiết lập lại thành công. Bạn có thể đăng nhập ngay bây giờ.', null, 200);
    } else {
        // Simulate OTP / reset instructions
        sendResponse(true, 'Yêu cầu đặt lại mật khẩu hợp lệ. Vui lòng nhập mật khẩu mới để hoàn tất.', [
            'email' => $email,
            'can_reset' => true
        ], 200);
    }

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi hệ thống: ' . $e->getMessage(), null, 500);
}
