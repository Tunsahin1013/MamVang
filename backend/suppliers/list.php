<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - SUPPLIERS LIST
 * Endpoint: GET /backend/suppliers/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

try {
    $pdo = Database::getConnection();

    $search = trim($_GET['search'] ?? '');
    $where = "WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $where .= " AND (name LIKE ? OR contact_person LIKE ? OR phone LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $sql = "SELECT s.*, 
                   (SELECT COUNT(*) FROM purchase_orders po WHERE po.supplier_id = s.id) AS total_orders
            FROM suppliers s
            {$where}
            ORDER BY s.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $suppliers = $stmt->fetchAll();

    sendResponse(true, 'Lấy danh sách nhà cung cấp thành công.', $suppliers, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
