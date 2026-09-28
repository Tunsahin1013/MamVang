<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CHANGE PASSWORD API
 * Endpoint: POST /backend/auth/change_password.php
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Phương thức không hợp lệ. Chỉ chấp nhận POST.', null, 405);
}

$currentUser = AuthMiddleware::authorize();
$input = getJsonInput();

$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    sendResponse(false, 'Vui lòng nhập mật khẩu hiện tại và mật khẩu mới.', null, 400);
}

if (strlen($newPassword) < 6) {
    sendResponse(false, 'Mật khẩu mới phải có ít nhất 6 ký tự.', null, 400);
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$currentUser['id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        sendResponse(false, 'Mật khẩu hiện tại không chính xác.', null, 400);
    }

    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmtUpdate->execute([$newHash, $currentUser['id']]);

    sendResponse(true, 'Đổi mật khẩu thành công! Vui lòng ghi nhớ mật khẩu mới.', null, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cập nhật mật khẩu: ' . $e->getMessage(), null, 500);
}
