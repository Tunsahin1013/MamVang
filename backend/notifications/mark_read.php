<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - MARK NOTIFICATIONS AS READ
 * Endpoint: POST /backend/notifications/mark_read.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();
$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

try {
    $pdo = Database::getConnection();

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $currentUser['id']]);
    } else {
        // Mark all as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$currentUser['id']]);
    }

    sendResponse(true, 'Đã cập nhật trạng thái thông báo.', null, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
