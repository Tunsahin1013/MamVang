<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - DELETE SUPPLIER (ADMIN ONLY)
 * Endpoint: POST/DELETE /backend/suppliers/delete.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID nhà cung cấp.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS total FROM purchase_orders WHERE supplier_id = ?");
    $stmtCheck->execute([$id]);
    if ((int)$stmtCheck->fetch()['total'] > 0) {
        $stmtDeact = $pdo->prepare("UPDATE suppliers SET status = 'INACTIVE' WHERE id = ?");
        $stmtDeact->execute([$id]);
        sendResponse(true, 'Nhà cung cấp đã có đơn nhập hàng nên được chuyển sang trạng thái Tạm ngưng (INACTIVE).', ['id' => $id], 200);
    } else {
        $stmtDel = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmtDel->execute([$id]);
        sendResponse(true, 'Xóa nhà cung cấp thành công.', ['id' => $id], 200);
    }

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
