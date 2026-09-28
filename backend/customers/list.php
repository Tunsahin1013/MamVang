<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CUSTOMERS LIST API (ADMIN & EMPLOYEE)
 * Endpoint: GET /backend/customers/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

try {
    $pdo = Database::getConnection();

    $search = trim($_GET['search'] ?? '');
    $where = "WHERE u.role = 'CUSTOMER'";
    $params = [];

    if (!empty($search)) {
        $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR c.phone LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $sql = "SELECT c.id AS customer_id, u.id AS user_id, u.name, u.email, u.avatar, u.status, u.created_at,
                   c.phone, c.address, c.points,
                   w.balance AS wallet_balance,
                   (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS total_orders,
                   (SELECT COALESCE(SUM(final_amount), 0) FROM orders o WHERE o.user_id = u.id AND o.order_status = 'COMPLETED') AS total_spent
            FROM customers c
            JOIN users u ON c.user_id = u.id
            LEFT JOIN wallets w ON u.id = w.user_id
            {$where}
            ORDER BY c.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();

    foreach ($customers as &$cust) {
        $cust['points'] = (int)$cust['points'];
        $cust['wallet_balance'] = (float)($cust['wallet_balance'] ?? 0);
        $cust['total_orders'] = (int)$cust['total_orders'];
        $cust['total_spent'] = (float)$cust['total_spent'];
    }

    sendResponse(true, 'Lấy danh sách Sinh Viên thành công.', $customers, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
