<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - TODAY ATTENDANCE STATUS
 * Endpoint: GET /backend/attendance/today.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['EMPLOYEE', 'ADMIN']);

try {
    $pdo = Database::getConnection();

    $stmtEmp = $pdo->prepare("SELECT id, department, position FROM employees WHERE user_id = ?");
    $stmtEmp->execute([$currentUser['id']]);
    $emp = $stmtEmp->fetch();

    if (!$emp) {
        sendResponse(true, 'Tài khoản không phải nhân viên.', [
            'is_employee' => false,
            'checked_in' => false,
            'checked_out' => false
        ], 200);
    }

    $employeeId = (int)$emp['id'];
    $today = date('Y-m-d');

    $stmtAtt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND DATE(check_in_time) = ? ORDER BY id DESC LIMIT 1");
    $stmtAtt->execute([$employeeId, $today]);
    $att = $stmtAtt->fetch();

    // Today shift
    $stmtShift = $pdo->prepare("SELECT s.*, es.status AS shift_status 
                                FROM employee_shifts es 
                                JOIN shifts s ON es.shift_id = s.id 
                                WHERE es.employee_id = ? AND es.shift_date = ?");
    $stmtShift->execute([$employeeId, $today]);
    $shift = $stmtShift->fetch();

    sendResponse(true, 'Trạng thái điểm danh hôm nay.', [
        'is_employee' => true,
        'today_date' => $today,
        'checked_in' => !empty($att),
        'checked_out' => !empty($att) && !empty($att['check_out_time']),
        'attendance' => $att ?: null,
        'assigned_shift' => $shift ?: null
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
