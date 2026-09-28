<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - EMPLOYEE CHECK-OUT API
 * Endpoint: POST /backend/attendance/check_out.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['EMPLOYEE', 'ADMIN']);

try {
    $pdo = Database::getConnection();

    $stmtEmp = $pdo->prepare("SELECT id FROM employees WHERE user_id = ?");
    $stmtEmp->execute([$currentUser['id']]);
    $emp = $stmtEmp->fetch();

    if (!$emp) {
        sendResponse(false, 'Không tìm thấy hồ sơ nhân viên.', null, 400);
    }

    $employeeId = (int)$emp['id'];
    $today = date('Y-m-d');

    // Find latest open check-in
    $stmtAtt = $pdo->prepare("SELECT id, check_in_time FROM attendance WHERE employee_id = ? AND check_out_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmtAtt->execute([$employeeId]);
    $attendance = $stmtAtt->fetch();

    if (!$attendance) {
        sendResponse(false, 'Bạn chưa Check-in hôm nay hoặc đã hoàn thành Check-out rồi.', null, 400);
    }

    $checkInTs = strtotime($attendance['check_in_time']);
    $nowTs = time();
    $secondsWorked = max(0, $nowTs - $checkInTs);
    $hoursWorked = round($secondsWorked / 3600, 2);

    $nowStr = date('Y-m-d H:i:s');

    $stmtUpd = $pdo->prepare("UPDATE attendance SET check_out_time = ?, total_hours = ? WHERE id = ?");
    $stmtUpd->execute([$nowStr, $hoursWorked, $attendance['id']]);

    sendResponse(true, "Check-out thành công lúc " . date('H:i:s d/m/Y') . "! Tổng thời gian làm việc ca này: {$hoursWorked} giờ.", [
        'attendance_id' => $attendance['id'],
        'check_in_time' => $attendance['check_in_time'],
        'check_out_time' => $nowStr,
        'total_hours' => $hoursWorked
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
