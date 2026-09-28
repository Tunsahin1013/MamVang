<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - DELETE CATEGORY (ADMIN ONLY)
 * Endpoint: POST/DELETE /backend/categories/delete.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID danh mục cần xóa.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Check if category has products
    $stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM products WHERE category_id = ?");
    $stmtCount->execute([$id]);
    $count = (int)$stmtCount->fetch()['total'];

    if ($count > 0) {
        sendResponse(false, "Không thể xóa danh mục này vì đang có {$count} món ăn/sản phẩm thuộc danh mục. Hãy chuyển sản phẩm sang danh mục khác trước.", null, 400);
    }

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    sendResponse(true, 'Xóa danh mục thành công.', ['id' => $id], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
