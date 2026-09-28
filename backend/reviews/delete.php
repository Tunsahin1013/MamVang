<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - DELETE / MODERATE REVIEW (ADMIN ONLY)
 * Endpoint: POST/DELETE /backend/reviews/delete.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID đánh giá cần xóa.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$id]);

    sendResponse(true, 'Xóa đánh giá thành công.', ['id' => $id], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
