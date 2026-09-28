<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - UPDATE CATEGORY (ADMIN ONLY)
 * Endpoint: PUT/POST /backend/categories/update.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID danh mục cần cập nhật.', null, 400);
}

$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');
$image = trim($input['image'] ?? '');
$status = $input['status'] ?? 'ACTIVE';

if (empty($name)) {
    sendResponse(false, 'Tên danh mục không được để trống.', null, 400);
}

try {
    $pdo = Database::getConnection();
    $stmtCheck = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $stmtCheck->execute([$id]);
    if (!$stmtCheck->fetch()) {
        sendResponse(false, 'Không tìm thấy danh mục.', null, 404);
    }

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, image = ?, status = ? WHERE id = ?");
    $stmt->execute([$name, $description, $image, $status, $id]);

    sendResponse(true, 'Cập nhật danh mục thành công.', [
        'id' => $id,
        'name' => $name,
        'description' => $description,
        'image' => $image,
        'status' => $status
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
