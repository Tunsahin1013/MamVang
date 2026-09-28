<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - ASSIGN EMPLOYEE TO SHIFT
 * Endpoint: POST /backend/shifts/assign.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$employeeId = (int)($input['employee_id'] ?? 0);
$shiftId = (int)($input['shift_id'] ?? 0);
$shiftDate = trim($input['shift_date'] ?? date('Y-m-d'));

if ($employeeId <= 0 || $shiftId <= 0 || empty($shiftDate)) {
    sendResponse(false, 'Nhân viên, ca làm và ngày làm việc là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Upsert or insert shift assignment
    $stmt = $pdo->prepare("INSERT INTO employee_shifts (employee_id, shift_id, shift_date, status) 
                           VALUES (?, ?, ?, 'SCHEDULED')
                           ON DUPLICATE KEY UPDATE status = 'SCHEDULED'");
    $stmt->execute([$employeeId, $shiftId, $shiftDate]);

    // Send notification to employee
    $stmtEmp = $pdo->prepare("SELECT user_id FROM employees WHERE id = ?");
    $stmtEmp->execute([$employeeId]);
    $emp = $stmtEmp->fetch();
    if ($emp) {
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, link) VALUES (?, 'Phân công ca làm mới', ?, 'SYSTEM', 0, '/canteen-management/frontend/employee/shifts.html')");
        $stmtNotif->execute([$emp['user_id'], "Bạn đã được phân công ca làm ngày " . date('d/m/Y', strtotime($shiftDate))]);
    }

    sendResponse(true, 'Phân công ca làm việc thành công.', [
        'employee_id' => $employeeId,
        'shift_id' => $shiftId,
        'shift_date' => $shiftDate
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
