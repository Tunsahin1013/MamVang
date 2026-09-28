<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - DELETE / ARCHIVE PRODUCT
 * Endpoint: POST/DELETE /backend/products/delete.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID món ăn cần xóa.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Check if product is in any orders
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS total FROM order_items WHERE product_id = ?");
    $stmtCheck->execute([$id]);
    $orderCount = (int)$stmtCheck->fetch()['total'];

    if ($orderCount > 0) {
        // Soft delete / archive to preserve historic accounting records
        $stmtArchive = $pdo->prepare("UPDATE products SET status = 'DISCONTINUED' WHERE id = ?");
        $stmtArchive->execute([$id]);
        sendResponse(true, "Món ăn đã có trong {$orderCount} đơn hàng lịch sử nên đã được chuyển sang trạng thái 'Ngừng kinh doanh'.", ['id' => $id, 'archived' => true], 200);
    } else {
        $stmtDelete = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmtDelete->execute([$id]);
        sendResponse(true, 'Xóa món ăn hoàn tất.', ['id' => $id, 'deleted' => true], 200);
    }

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
