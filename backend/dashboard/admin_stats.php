<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - ADMIN DASHBOARD OVERVIEW STATS
 * Endpoint: GET /backend/dashboard/admin_stats.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

try {
    $pdo = Database::getConnection();

    // 1. KPI Counts
    $revenue = (float)$pdo->query("SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE order_status = 'COMPLETED'")->fetchColumn();
    $todayRevenue = (float)$pdo->query("SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE order_status = 'COMPLETED' AND DATE(created_at) = CURDATE()")->fetchColumn();
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('PENDING', 'CONFIRMED', 'PREPARING')")->fetchColumn();
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'AVAILABLE'")->fetchColumn();
    $lowStockCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 10 AND status = 'AVAILABLE'")->fetchColumn();
    $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'CUSTOMER'")->fetchColumn();
    $totalEmployees = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'EMPLOYEE'")->fetchColumn();

    // 2. Revenue chart (last 7 days)
    $stmtChart = $pdo->query("SELECT 
        DATE(created_at) AS date_label,
        COALESCE(SUM(final_amount), 0) AS revenue,
        COUNT(*) AS orders_count
        FROM orders 
        WHERE order_status = 'COMPLETED' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date_label ASC");
    $chartData = $stmtChart->fetchAll();

    // 3. Orders by Status
    $stmtStatus = $pdo->query("SELECT order_status, COUNT(*) AS count FROM orders GROUP BY order_status");
    $ordersByStatus = $stmtStatus->fetchAll();

    // 4. Recent Orders (Top 5)
    $stmtRecent = $pdo->query("SELECT o.id, o.order_number, o.final_amount, o.order_status, o.payment_status, o.table_number, o.created_at, u.name AS customer_name
                               FROM orders o
                               JOIN users u ON o.user_id = u.id
                               ORDER BY o.id DESC LIMIT 6");
    $recentOrders = $stmtRecent->fetchAll();

    // 5. Best selling products (Top 5)
    $stmtBest = $pdo->query("SELECT p.id, p.name, p.price, p.image, COALESCE(SUM(oi.quantity), 0) AS total_sold
                             FROM products p
                             JOIN order_items oi ON p.id = oi.product_id
                             GROUP BY p.id
                             ORDER BY total_sold DESC LIMIT 5");
    $topProducts = $stmtBest->fetchAll();

    sendResponse(true, 'Thống kê tổng quan bảng điều khiển.', [
        'kpis' => [
            'total_revenue' => $revenue,
            'today_revenue' => $todayRevenue,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'total_customers' => $totalCustomers,
            'total_employees' => $totalEmployees
        ],
        'revenue_chart' => $chartData,
        'orders_by_status' => $ordersByStatus,
        'recent_orders' => $recentOrders,
        'top_products' => $topProducts
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
