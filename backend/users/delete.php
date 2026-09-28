<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - DELETE USER (ADMIN ONLY)
 * Endpoint: POST/DELETE /backend/users/delete.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$admin = AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID người dùng cần xóa.', null, 400);
}

if ($id === (int)$admin['id']) {
    sendResponse(false, 'Bạn không thể tự xóa tài khoản quản trị của chính mình.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Check if user has orders
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS total FROM orders WHERE user_id = ?");
    $stmtCheck->execute([$id]);
    $orderCount = (int)$stmtCheck->fetch()['total'];

    if ($orderCount > 0) {
        // Suspend user instead of hard deleting to preserve billing/order relationships
        $stmtSuspend = $pdo->prepare("UPDATE users SET status = 'SUSPENDED' WHERE id = ?");
        $stmtSuspend->execute([$id]);
        sendResponse(true, "Người dùng đã có {$orderCount} đơn hàng trong lịch sử nên đã được chuyển sang trạng thái 'Đã khóa' (SUSPENDED).", ['id' => $id, 'suspended' => true], 200);
    } else {
        $stmtDel = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmtDel->execute([$id]);
        sendResponse(true, 'Xóa tài khoản người dùng thành công.', ['id' => $id, 'deleted' => true], 200);
    }

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
