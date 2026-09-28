<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - DELETE EMPLOYEE (ADMIN ONLY)
 * Endpoint: POST/DELETE /backend/employees/delete.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$employeeId = (int)($input['employee_id'] ?? ($_GET['id'] ?? 0));

if ($employeeId <= 0) {
    sendResponse(false, 'Thiếu ID nhân viên cần xóa.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT user_id FROM employees WHERE id = ?");
    $stmt->execute([$employeeId]);
    $emp = $stmt->fetch();

    if (!$emp) {
        sendResponse(false, 'Không tìm thấy nhân viên.', null, 404);
    }

    $pdo->beginTransaction();

    // Mark employee as RESIGNED and user account as INACTIVE
    $stmtUpdEmp = $pdo->prepare("UPDATE employees SET status = 'RESIGNED' WHERE id = ?");
    $stmtUpdEmp->execute([$employeeId]);

    $stmtUpdUser = $pdo->prepare("UPDATE users SET status = 'INACTIVE' WHERE id = ?");
    $stmtUpdUser->execute([$emp['user_id']]);

    $pdo->commit();

    sendResponse(true, 'Đã chuyển nhân viên sang trạng thái Đã nghỉ việc (RESIGNED).', ['employee_id' => $employeeId], 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
