<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - REVENUE REPORT API (ADMIN ONLY)
 * Endpoint: GET /backend/reports/revenue.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

try {
    $pdo = Database::getConnection();

    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');

    // 1. Overall Totals
    $stmtTotals = $pdo->prepare("SELECT 
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_amount), 0) AS gross_revenue,
        COALESCE(SUM(discount_amount), 0) AS total_discounts,
        COALESCE(SUM(final_amount), 0) AS net_revenue,
        COALESCE(AVG(final_amount), 0) AS average_order_value
        FROM orders 
        WHERE order_status = 'COMPLETED' AND DATE(created_at) BETWEEN ? AND ?");
    $stmtTotals->execute([$startDate, $endDate]);
    $totals = $stmtTotals->fetch();

    // 2. Daily revenue breakdown for chart
    $stmtDaily = $pdo->prepare("SELECT 
        DATE(created_at) AS report_date,
        COUNT(*) AS order_count,
        COALESCE(SUM(final_amount), 0) AS daily_revenue
        FROM orders
        WHERE order_status = 'COMPLETED' AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY report_date ASC");
    $stmtDaily->execute([$startDate, $endDate]);
    $dailyBreakdown = $stmtDaily->fetchAll();

    // 3. Payment method distribution
    $stmtPayments = $pdo->prepare("SELECT 
        payment_method,
        COUNT(*) AS count,
        COALESCE(SUM(final_amount), 0) AS total_amount
        FROM orders
        WHERE order_status = 'COMPLETED' AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY payment_method");
    $stmtPayments->execute([$startDate, $endDate]);
    $paymentMethods = $stmtPayments->fetchAll();

    sendResponse(true, 'Báo cáo doanh thu thành công.', [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'totals' => [
            'total_orders' => (int)$totals['total_orders'],
            'gross_revenue' => (float)$totals['gross_revenue'],
            'total_discounts' => (float)$totals['total_discounts'],
            'net_revenue' => (float)$totals['net_revenue'],
            'average_order_value' => round((float)$totals['average_order_value'], 0)
        ],
        'daily_breakdown' => $dailyBreakdown,
        'payment_methods' => $paymentMethods
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
