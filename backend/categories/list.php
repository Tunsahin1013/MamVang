<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CATEGORIES LIST API
 * Endpoint: GET /backend/categories/list.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();
    
    $status = $_GET['status'] ?? 'ACTIVE';
    $where = "WHERE 1=1";
    $params = [];

    if ($status !== 'ALL') {
        $where .= " AND c.status = ?";
        $params[] = $status;
    }

    $sql = "SELECT c.*, COUNT(p.id) AS product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id AND p.status != 'DISCONTINUED'
            {$where}
            GROUP BY c.id 
            ORDER BY c.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll();

    sendResponse(true, 'Lấy danh mục thành công.', $categories, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
