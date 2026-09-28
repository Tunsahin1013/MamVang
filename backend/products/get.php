<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - GET PRODUCT DETAIL
 * Endpoint: GET /backend/products/get.php?id=1
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = trim($_GET['slug'] ?? '');

if ($id <= 0 && empty($slug)) {
    sendResponse(false, 'Thiếu ID hoặc slug của món ăn.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $where = $id > 0 ? "p.id = ?" : "p.slug = ?";
    $param = $id > 0 ? $id : $slug;

    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   COALESCE(AVG(r.rating), 5.0) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'APPROVED'
            WHERE {$where}
            GROUP BY p.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$param]);
    $product = $stmt->fetch();

    if (!$product) {
        sendResponse(false, 'Không tìm thấy món ăn này.', null, 404);
    }

    $product['price'] = (float)$product['price'];
    $product['cost_price'] = (float)$product['cost_price'];
    $product['stock_quantity'] = (int)$product['stock_quantity'];
    $product['avg_rating'] = round((float)$product['avg_rating'], 1);
    $product['review_count'] = (int)$product['review_count'];
    $product['is_featured'] = (bool)$product['is_featured'];

    // Fetch approved reviews for this product
    $stmtReviews = $pdo->prepare("SELECT r.id, r.rating, r.comment, r.created_at, u.name AS user_name, u.avatar AS user_avatar
                                  FROM reviews r
                                  JOIN users u ON r.user_id = u.id
                                  WHERE r.product_id = ? AND r.status = 'APPROVED'
                                  ORDER BY r.id DESC LIMIT 20");
    $stmtReviews->execute([$product['id']]);
    $product['reviews'] = $stmtReviews->fetchAll();

    sendResponse(true, 'Chi tiết món ăn.', $product, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
