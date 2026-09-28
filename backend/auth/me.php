<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - GET CURRENT AUTHENTICATED USER
 * Endpoint: GET /backend/auth/me.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();

try {
    $pdo = Database::getConnection();

    if ($currentUser['role'] === 'CUSTOMER') {
        $stmt = $pdo->prepare("SELECT phone, address, points FROM customers WHERE user_id = ?");
        $stmt->execute([$currentUser['id']]);
        $profile = $stmt->fetch();
        $currentUser['customer_profile'] = $profile ?: null;
        $currentUser['points'] = $profile ? (int)$profile['points'] : 0;

        $stmtWal = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $stmtWal->execute([$currentUser['id']]);
        $wal = $stmtWal->fetch();
        $currentUser['wallet_balance'] = $wal ? (float)$wal['balance'] : 0.00;
    } elseif ($currentUser['role'] === 'EMPLOYEE') {
        $stmt = $pdo->prepare("SELECT phone, department, position, salary, hire_date FROM employees WHERE user_id = ?");
        $stmt->execute([$currentUser['id']]);
        $currentUser['employee_profile'] = $stmt->fetch() ?: null;
    }

    // Unread notifications count
    $stmtNotif = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmtNotif->execute([$currentUser['id']]);
    $currentUser['unread_notifications'] = (int)($stmtNotif->fetch()['unread_count'] ?? 0);

    sendResponse(true, 'Lấy thông tin người dùng thành công.', $currentUser, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi truy vấn: ' . $e->getMessage(), null, 500);
}
