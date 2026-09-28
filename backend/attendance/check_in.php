<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - EMPLOYEE CHECK-IN API
 * Endpoint: POST /backend/attendance/check_in.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['EMPLOYEE', 'ADMIN']);

try {
    $pdo = Database::getConnection();

    // Get employee record
    $stmtEmp = $pdo->prepare("SELECT id FROM employees WHERE user_id = ?");
    $stmtEmp->execute([$currentUser['id']]);
    $emp = $stmtEmp->fetch();

    if (!$emp) {
        sendResponse(false, 'Không tìm thấy hồ sơ nhân viên liên kết với tài khoản này.', null, 400);
    }

    $employeeId = (int)$emp['id'];
    $today = date('Y-m-d');

    // Check if already checked in today without checking out
    $stmtCheck = $pdo->prepare("SELECT id, check_in_time, check_out_time FROM attendance WHERE employee_id = ? AND DATE(check_in_time) = ? AND check_out_time IS NULL");
    $stmtCheck->execute([$employeeId, $today]);
    if ($stmtCheck->fetch()) {
        sendResponse(false, 'Bạn đã điểm danh Check-in hôm nay rồi và chưa Check-out.', null, 400);
    }

    // Determine status: check scheduled shift
    $currentTime = date('H:i:s');
    $status = 'PRESENT';
    $notes = 'Điểm danh trực tiếp tại quầy';

    $stmtShift = $pdo->prepare("SELECT s.start_time, s.name 
                                FROM employee_shifts es 
                                JOIN shifts s ON es.shift_id = s.id 
                                WHERE es.employee_id = ? AND es.shift_date = ?");
    $stmtShift->execute([$employeeId, $today]);
    $shift = $stmtShift->fetch();

    if ($shift) {
        // If checked in > 15 mins after shift start, mark as LATE
        $shiftStart = strtotime("{$today} {$shift['start_time']}");
        $nowTs = time();
        if ($nowTs > ($shiftStart + 900)) {
            $status = 'LATE';
            $minutesLate = round(($nowTs - $shiftStart) / 60);
            $notes = "Đi trễ {$minutesLate} phút so với {$shift['name']}";
        }
    }

    $nowStr = date('Y-m-d H:i:s');
    $stmtInsert = $pdo->prepare("INSERT INTO attendance (employee_id, check_in_time, status, notes) VALUES (?, ?, ?, ?)");
    $stmtInsert->execute([$employeeId, $nowStr, $status, $notes]);
    $attId = (int)$pdo->lastInsertId();

    sendResponse(true, "Check-in thành công lúc " . date('H:i:s d/m/Y') . " (" . ($status === 'LATE' ? 'Đi trễ' : 'Đúng giờ') . "). Chúc bạn một ca làm việc vui vẻ!", [
        'attendance_id' => $attId,
        'check_in_time' => $nowStr,
        'status' => $status,
        'notes' => $notes
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
