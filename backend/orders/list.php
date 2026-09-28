<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - ORDERS LIST API
 * Endpoint: GET /backend/orders/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();

try {
    $pdo = Database::getConnection();

    $status = $_GET['status'] ?? 'ALL';
    $search = trim($_GET['search'] ?? '');
    $date = trim($_GET['date'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $where = "WHERE 1=1";
    $params = [];

    // Role check: CUSTOMER can ONLY see their own orders!
    if ($currentUser['role'] === 'CUSTOMER') {
        $where .= " AND o.user_id = ?";
        $params[] = $currentUser['id'];
    }

    if ($status !== 'ALL') {
        $where .= " AND o.order_status = ?";
        $params[] = $status;
    }

    if (!empty($search)) {
        $where .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR o.table_number LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    if (!empty($date)) {
        $where .= " AND DATE(o.created_at) = ?";
        $params[] = $date;
    }

    // Count total
    $countSql = "SELECT COUNT(*) as total 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 {$where}";
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetch()['total'];

    // Main query
    $sql = "SELECT o.*, u.name AS customer_name, u.email AS customer_email,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
                   (SELECT GROUP_CONCAT(CONCAT(product_name, ' (x', quantity, ')') SEPARATOR ', ') 
                    FROM order_items oi WHERE oi.order_id = o.id) AS items_summary
            FROM orders o
            JOIN users u ON o.user_id = u.id
            {$where}
            ORDER BY o.id DESC
            LIMIT {$limit} OFFSET {$offset}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    foreach ($orders as &$ord) {
        $ord['total_amount'] = (float)$ord['total_amount'];
        $ord['discount_amount'] = (float)$ord['discount_amount'];
        $ord['final_amount'] = (float)$ord['final_amount'];
        $ord['item_count'] = (int)$ord['item_count'];
    }

    sendResponse(true, 'Lấy danh sách đơn hàng thành công.', [
        'items' => $orders,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
