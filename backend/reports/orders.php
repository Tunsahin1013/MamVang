<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - ORDERS REPORT API (ADMIN ONLY)
 * Endpoint: GET /backend/reports/orders.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

try {
    $pdo = Database::getConnection();

    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');

    // 1. Orders by Status
    $stmtStatus = $pdo->prepare("SELECT 
        order_status,
        COUNT(*) AS count,
        COALESCE(SUM(final_amount), 0) AS total_value
        FROM orders
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY order_status");
    $stmtStatus->execute([$startDate, $endDate]);
    $byStatus = $stmtStatus->fetchAll();

    // 2. Orders hourly peak time analysis
    $stmtHours = $pdo->prepare("SELECT 
        HOUR(created_at) AS hour_of_day,
        COUNT(*) AS order_count
        FROM orders
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY HOUR(created_at)
        ORDER BY hour_of_day ASC");
    $stmtHours->execute([$startDate, $endDate]);
    $peakHours = $stmtHours->fetchAll();

    sendResponse(true, 'Báo cáo đơn hàng.', [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'by_status' => $byStatus,
        'peak_hours' => $peakHours
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
