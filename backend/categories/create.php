<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CREATE CATEGORY (ADMIN ONLY)
 * Endpoint: POST /backend/categories/create.php
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

// Require ADMIN role
AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');
$image = trim($input['image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80');
$status = $input['status'] ?? 'ACTIVE';

if (empty($name)) {
    sendResponse(false, 'Tên danh mục không được để trống.', null, 400);
}

// Auto-generate slug
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
if (empty($slug)) {
    $slug = 'danh-muc-' . time();
}

try {
    $pdo = Database::getConnection();

    // Check duplicate
    $stmtCheck = $pdo->prepare("SELECT id FROM categories WHERE name = ? OR slug = ?");
    $stmtCheck->execute([$name, $slug]);
    if ($stmtCheck->fetch()) {
        sendResponse(false, 'Danh mục với tên này đã tồn tại.', null, 409);
    }

    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, image, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $description, $image, $status]);
    $newId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Thêm danh mục thành công.', [
        'id' => $newId,
        'name' => $name,
        'slug' => $slug,
        'description' => $description,
        'image' => $image,
        'status' => $status
    ], 201);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
