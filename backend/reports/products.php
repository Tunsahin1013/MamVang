<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - PRODUCTS SALES REPORT (BEST SELLERS & SLOW MOVERS)
 * Endpoint: GET /backend/reports/products.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

try {
    $pdo = Database::getConnection();

    // Best selling products (Top 10)
    $stmtBest = $pdo->query("SELECT p.id, p.name, p.price, p.image, c.name AS category_name,
                                    COALESCE(SUM(oi.quantity), 0) AS total_sold,
                                    COALESCE(SUM(oi.subtotal), 0) AS total_revenue
                             FROM products p
                             JOIN categories c ON p.category_id = c.id
                             JOIN order_items oi ON p.id = oi.product_id
                             JOIN orders o ON oi.order_id = o.id AND o.order_status = 'COMPLETED'
                             GROUP BY p.id
                             ORDER BY total_sold DESC
                             LIMIT 10");
    $bestSellers = $stmtBest->fetchAll();

    // Slow moving products (Sold < 5 or 0)
    $stmtSlow = $pdo->query("SELECT p.id, p.name, p.price, p.stock_quantity, c.name AS category_name,
                                    COALESCE(SUM(oi.quantity), 0) AS total_sold
                             FROM products p
                             JOIN categories c ON p.category_id = c.id
                             LEFT JOIN order_items oi ON p.id = oi.product_id
                             WHERE p.status = 'AVAILABLE'
                             GROUP BY p.id
                             HAVING total_sold <= 2
                             ORDER BY total_sold ASC, p.stock_quantity DESC
                             LIMIT 10");
    $slowMovers = $stmtSlow->fetchAll();

    sendResponse(true, 'Báo cáo hiệu suất sản phẩm.', [
        'best_sellers' => $bestSellers,
        'slow_movers' => $slowMovers
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
