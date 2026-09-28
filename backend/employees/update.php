<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - UPDATE EMPLOYEE (ADMIN ONLY)
 * Endpoint: POST/PUT /backend/employees/update.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$employeeId = (int)($input['employee_id'] ?? ($_GET['id'] ?? 0));

if ($employeeId <= 0) {
    sendResponse(false, 'Thiếu ID nhân viên cần cập nhật.', null, 400);
}

$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? '');
$department = trim($input['department'] ?? '');
$position = trim($input['position'] ?? '');
$salary = isset($input['salary']) ? (float)$input['salary'] : null;
$status = $input['status'] ?? null;

try {
    $pdo = Database::getConnection();

    $stmtCheck = $pdo->prepare("SELECT user_id FROM employees WHERE id = ?");
    $stmtCheck->execute([$employeeId]);
    $emp = $stmtCheck->fetch();

    if (!$emp) {
        sendResponse(false, 'Không tìm thấy hồ sơ nhân viên.', null, 404);
    }

    $pdo->beginTransaction();

    if (!empty($name)) {
        $stmtUser = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmtUser->execute([$name, $emp['user_id']]);
    }

    $stmtUpdEmp = $pdo->prepare("UPDATE employees SET phone = ?, department = ?, position = ?, salary = COALESCE(?, salary), status = COALESCE(?, status) WHERE id = ?");
    $stmtUpdEmp->execute([$phone, $department, $position, $salary, $status, $employeeId]);

    $pdo->commit();

    sendResponse(true, 'Cập nhật nhân viên thành công.', ['employee_id' => $employeeId], 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
