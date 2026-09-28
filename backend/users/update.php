<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - UPDATE USER (ADMIN ONLY)
 * Endpoint: POST/PUT /backend/users/update.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID người dùng cần cập nhật.', null, 400);
}

$name = trim($input['name'] ?? '');
$role = $input['role'] ?? null;
$status = $input['status'] ?? null;
$password = $input['password'] ?? null;
$phone = trim($input['phone'] ?? '');

if (empty($name)) {
    sendResponse(false, 'Tên người dùng không được rỗng.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmtCheck = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmtCheck->execute([$id]);
    $user = $stmtCheck->fetch();

    if (!$user) {
        sendResponse(false, 'Người dùng không tồn tại.', null, 404);
    }

    $pdo->beginTransaction();

    $newRole = $role ?: $user['role'];
    $newStatus = $status ?: $user['status'];

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmtUpd = $pdo->prepare("UPDATE users SET name = ?, role = ?, status = ?, password = ? WHERE id = ?");
        $stmtUpd->execute([$name, $newRole, $newStatus, $hash, $id]);
    } else {
        $stmtUpd = $pdo->prepare("UPDATE users SET name = ?, role = ?, status = ? WHERE id = ?");
        $stmtUpd->execute([$name, $newRole, $newStatus, $id]);
    }

    // Update customer / employee phone
    if ($user['role'] === 'CUSTOMER') {
        $stmtCust = $pdo->prepare("UPDATE customers SET phone = ? WHERE user_id = ?");
        $stmtCust->execute([$phone, $id]);
    } elseif ($user['role'] === 'EMPLOYEE') {
        $stmtEmp = $pdo->prepare("UPDATE employees SET phone = ? WHERE user_id = ?");
        $stmtEmp->execute([$phone, $id]);
    }

    $pdo->commit();

    sendResponse(true, 'Cập nhật thông tin người dùng thành công.', [
        'id' => $id,
        'name' => $name,
        'role' => $newRole,
        'status' => $newStatus
    ], 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cập nhật người dùng: ' . $e->getMessage(), null, 500);
}
