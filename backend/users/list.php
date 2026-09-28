<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - USERS MANAGEMENT (ADMIN ONLY)
 * Endpoint: GET /backend/users/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

try {
    $pdo = Database::getConnection();

    $role = $_GET['role'] ?? 'ALL';
    $search = trim($_GET['search'] ?? '');
    $status = $_GET['status'] ?? 'ALL';

    $where = "WHERE 1=1";
    $params = [];

    if ($role !== 'ALL') {
        $where .= " AND u.role = ?";
        $params[] = $role;
    }

    if ($status !== 'ALL') {
        $where .= " AND u.status = ?";
        $params[] = $status;
    }

    if (!empty($search)) {
        $where .= " AND (u.name LIKE ? OR u.email LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $sql = "SELECT u.id, u.name, u.email, u.role, u.avatar, u.status, u.created_at,
                   c.phone AS customer_phone, c.points,
                   e.phone AS employee_phone, e.department, e.position,
                   w.balance AS wallet_balance
            FROM users u
            LEFT JOIN customers c ON u.id = c.user_id
            LEFT JOIN employees e ON u.id = e.user_id
            LEFT JOIN wallets w ON u.id = w.user_id
            {$where}
            ORDER BY u.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    foreach ($users as &$u) {
        $u['wallet_balance'] = $u['wallet_balance'] !== null ? (float)$u['wallet_balance'] : 0.00;
        $u['points'] = $u['points'] !== null ? (int)$u['points'] : 0;
    }

    sendResponse(true, 'Lấy danh sách người dùng thành công.', $users, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
