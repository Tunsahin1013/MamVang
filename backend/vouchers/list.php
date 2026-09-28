<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - VOUCHERS LIST
 * Endpoint: GET /backend/vouchers/list.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';

// Public/Customer can list active vouchers, Admin can list all
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$isAdmin = false;
if (preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
    $payload = JWT::decode($matches[1]);
    if ($payload && isset($payload['role']) && $payload['role'] === 'ADMIN') {
        $isAdmin = true;
    }
}

try {
    $pdo = Database::getConnection();

    $where = "WHERE 1=1";
    if (!$isAdmin) {
        $where .= " AND status = 'ACTIVE' AND end_date >= CURDATE() AND times_used < usage_limit";
    }

    $stmt = $pdo->query("SELECT * FROM vouchers {$where} ORDER BY id DESC");
    $vouchers = $stmt->fetchAll();

    foreach ($vouchers as &$v) {
        $v['discount_value'] = (float)$v['discount_value'];
        $v['min_order_amount'] = (float)$v['min_order_amount'];
        $v['max_discount_amount'] = $v['max_discount_amount'] !== null ? (float)$v['max_discount_amount'] : null;
        $v['usage_limit'] = (int)$v['usage_limit'];
        $v['times_used'] = (int)$v['times_used'];
    }

    sendResponse(true, 'Lấy danh sách mã giảm giá thành công.', $vouchers, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
