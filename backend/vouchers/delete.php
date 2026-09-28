<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - DELETE VOUCHER (ADMIN ONLY)
 * Endpoint: POST/DELETE /backend/vouchers/delete.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID voucher.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = ?");
    $stmt->execute([$id]);

    sendResponse(true, 'Xóa voucher thành công.', ['id' => $id], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
