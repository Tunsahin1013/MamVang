<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - LOW STOCK ALERTS
 * Endpoint: GET /backend/inventory/low_stock.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

try {
    $pdo = Database::getConnection();

    $threshold = (int)($_GET['threshold'] ?? 10);

    $sql = "SELECT p.id, p.name, p.slug, p.price, p.stock_quantity, p.image, p.status, c.name AS category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.status != 'DISCONTINUED' AND p.stock_quantity <= ?
            ORDER BY p.stock_quantity ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$threshold]);
    $items = $stmt->fetchAll();

    foreach ($items as &$it) {
        $it['price'] = (float)$it['price'];
        $it['stock_quantity'] = (int)$it['stock_quantity'];
    }

    sendResponse(true, "Danh sách món ăn sắp hết hàng (<= {$threshold} phần).", [
        'threshold' => $threshold,
        'count' => count($items),
        'items' => $items
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
