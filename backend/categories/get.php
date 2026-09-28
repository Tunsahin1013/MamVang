<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - GET CATEGORY DETAIL
 * Endpoint: GET /backend/categories/get.php?id=1
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = trim($_GET['slug'] ?? '');

if ($id <= 0 && empty($slug)) {
    sendResponse(false, 'Thiếu ID hoặc slug của danh mục.', null, 400);
}

try {
    $pdo = Database::getConnection();
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
    }
    
    $category = $stmt->fetch();
    if (!$category) {
        sendResponse(false, 'Không tìm thấy danh mục.', null, 404);
    }

    sendResponse(true, 'Chi tiết danh mục.', $category, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi hệ thống: ' . $e->getMessage(), null, 500);
}
