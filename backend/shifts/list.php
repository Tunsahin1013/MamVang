<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - SHIFTS LIST API
 * Endpoint: GET /backend/shifts/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

try {
    $pdo = Database::getConnection();

    // 1. Shift templates
    $stmtShifts = $pdo->query("SELECT * FROM shifts ORDER BY start_time ASC");
    $shifts = $stmtShifts->fetchAll();

    // 2. Scheduled assignments for current week / upcoming days
    $startDate = $_GET['start_date'] ?? date('Y-m-d');
    $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'));

    $sqlAssignments = "SELECT es.id, es.shift_date, es.status,
                              s.id AS shift_id, s.name AS shift_name, s.start_time, s.end_time,
                              e.id AS employee_id, u.name AS employee_name, e.department, e.position
                       FROM employee_shifts es
                       JOIN shifts s ON es.shift_id = s.id
                       JOIN employees e ON es.employee_id = e.id
                       JOIN users u ON e.user_id = u.id
                       WHERE es.shift_date BETWEEN ? AND ?
                       ORDER BY es.shift_date ASC, s.start_time ASC";

    $stmtAssign = $pdo->prepare($sqlAssignments);
    $stmtAssign->execute([$startDate, $endDate]);
    $assignments = $stmtAssign->fetchAll();

    sendResponse(true, 'Lấy danh sách ca làm việc thành công.', [
        'shifts' => $shifts,
        'assignments' => $assignments
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
