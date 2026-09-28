<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - INVENTORY VALUATION & AUDIT REPORT
 * Endpoint: GET /backend/reports/inventory.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

try {
    $pdo = Database::getConnection();

    $stmtSummary = $pdo->query("SELECT 
        COUNT(*) AS total_skus,
        COALESCE(SUM(stock_quantity), 0) AS total_units_in_stock,
        COALESCE(SUM(stock_quantity * cost_price), 0) AS total_inventory_cost_value,
        COALESCE(SUM(stock_quantity * price), 0) AS total_retail_value,
        SUM(CASE WHEN stock_quantity <= 10 THEN 1 ELSE 0 END) AS low_stock_items_count,
        SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) AS out_of_stock_items_count
        FROM products 
        WHERE status != 'DISCONTINUED'");
    $summary = $stmtSummary->fetch();

    $stmtCatVal = $pdo->query("SELECT c.name AS category_name,
                                      COUNT(p.id) AS product_count,
                                      COALESCE(SUM(p.stock_quantity), 0) AS total_stock,
                                      COALESCE(SUM(p.stock_quantity * p.cost_price), 0) AS category_value
                               FROM categories c
                               JOIN products p ON c.id = p.category_id AND p.status != 'DISCONTINUED'
                               GROUP BY c.id
                               ORDER BY category_value DESC");
    $byCategory = $stmtCatVal->fetchAll();

    sendResponse(true, 'Báo cáo giá trị tồn kho.', [
        'summary' => $summary,
        'by_category' => $byCategory
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
