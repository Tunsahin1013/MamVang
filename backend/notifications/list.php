<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - NOTIFICATIONS LIST
 * Endpoint: GET /backend/notifications/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 50");
    $stmt->execute([$currentUser['id']]);
    $notifications = $stmt->fetchAll();

    $stmtCount = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmtCount->execute([$currentUser['id']]);
    $unreadCount = (int)$stmtCount->fetch()['unread_count'];

    sendResponse(true, 'Lấy danh sách thông báo thành công.', [
        'unread_count' => $unreadCount,
        'items' => $notifications
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
