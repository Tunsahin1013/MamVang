<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - UPDATE CUSTOMER PROFILE
 * Endpoint: POST/PUT /backend/customers/update.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();
$input = getJsonInput();

$targetUserId = (int)($input['user_id'] ?? $currentUser['id']);

// Only ADMIN can edit another customer's profile or add points
if ($currentUser['role'] !== 'ADMIN' && $targetUserId !== (int)$currentUser['id']) {
    sendResponse(false, 'Bạn chỉ có quyền cập nhật hồ sơ của chính mình.', null, 403);
}

$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');
$points = isset($input['points']) ? (int)$input['points'] : null;

try {
    $pdo = Database::getConnection();

    $pdo->beginTransaction();

    if (!empty($name)) {
        $stmtUser = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmtUser->execute([$name, $targetUserId]);
    }

    // Update customer table
    if ($currentUser['role'] === 'ADMIN' && $points !== null) {
        $stmtCust = $pdo->prepare("UPDATE customers SET phone = ?, address = ?, points = ? WHERE user_id = ?");
        $stmtCust->execute([$phone, $address, $points, $targetUserId]);
    } else {
        $stmtCust = $pdo->prepare("UPDATE customers SET phone = ?, address = ? WHERE user_id = ?");
        $stmtCust->execute([$phone, $address, $targetUserId]);
    }

    $pdo->commit();

    sendResponse(true, 'Cập nhật thông tin Sinh Viên thành công.', [
        'user_id' => $targetUserId,
        'name' => $name,
        'phone' => $phone,
        'address' => $address
    ], 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
