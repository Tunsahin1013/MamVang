<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CREATE EMPLOYEE (ADMIN ONLY)
 * Endpoint: POST /backend/employees/create.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? 'Employee@123';
$phone = trim($input['phone'] ?? '');
$department = trim($input['department'] ?? 'Phục vụ & Pha chế');
$position = trim($input['position'] ?? 'Nhân viên');
$salary = (float)($input['salary'] ?? 7500000.00);
$hireDate = !empty($input['hire_date']) ? $input['hire_date'] : date('Y-m-d');

if (empty($name) || empty($email)) {
    sendResponse(false, 'Họ tên và email nhân viên là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetch()) {
        sendResponse(false, 'Email này đã được sử dụng.', null, 409);
    }

    $pdo->beginTransaction();

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $avatar = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80';

    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password, role, avatar, status) VALUES (?, ?, ?, 'EMPLOYEE', ?, 'ACTIVE')");
    $stmtUser->execute([$name, $email, $hash, $avatar]);
    $userId = (int)$pdo->lastInsertId();

    $stmtEmp = $pdo->prepare("INSERT INTO employees (user_id, phone, department, position, salary, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE')");
    $stmtEmp->execute([$userId, $phone, $department, $position, $salary, $hireDate]);
    $empId = (int)$pdo->lastInsertId();

    $pdo->commit();

    sendResponse(true, 'Thêm nhân viên mới thành công.', [
        'employee_id' => $empId,
        'user_id' => $userId,
        'name' => $name,
        'email' => $email,
        'department' => $department,
        'position' => $position,
        'salary' => $salary
    ], 201);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi tạo nhân viên: ' . $e->getMessage(), null, 500);
}
