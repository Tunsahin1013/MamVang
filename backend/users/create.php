<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CREATE USER (ADMIN ONLY)
 * Endpoint: POST /backend/users/create.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? 'Canteen@123';
$role = $input['role'] ?? 'CUSTOMER';
$status = $input['status'] ?? 'ACTIVE';
$phone = trim($input['phone'] ?? '');
$department = trim($input['department'] ?? 'Phục vụ');
$position = trim($input['position'] ?? 'Nhân viên');
$salary = (float)($input['salary'] ?? 7000000.00);

if (empty($name) || empty($email) || empty($password)) {
    sendResponse(false, 'Tên, email và mật khẩu là bắt buộc.', null, 400);
}

if (!in_array($role, ['ADMIN', 'EMPLOYEE', 'CUSTOMER'])) {
    sendResponse(false, 'Vai trò không hợp lệ.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetch()) {
        sendResponse(false, 'Email này đã tồn tại trong hệ thống.', null, 409);
    }

    $pdo->beginTransaction();

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80';

    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password, role, avatar, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtUser->execute([$name, $email, $hash, $role, $avatar, $status]);
    $newUserId = (int)$pdo->lastInsertId();

    if ($role === 'CUSTOMER') {
        $stmtCust = $pdo->prepare("INSERT INTO customers (user_id, phone, address, points) VALUES (?, ?, '', 0)");
        $stmtCust->execute([$newUserId, $phone]);

        $stmtWal = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)");
        $stmtWal->execute([$newUserId]);
    } elseif ($role === 'EMPLOYEE') {
        $stmtEmp = $pdo->prepare("INSERT INTO employees (user_id, phone, department, position, salary, hire_date, status) VALUES (?, ?, ?, ?, ?, CURDATE(), 'ACTIVE')");
        $stmtEmp->execute([$newUserId, $phone, $department, $position, $salary]);
    }

    $pdo->commit();

    sendResponse(true, 'Tạo tài khoản người dùng thành công.', [
        'id' => $newUserId,
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'status' => $status
    ], 201);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi tạo người dùng: ' . $e->getMessage(), null, 500);
}
