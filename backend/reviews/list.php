<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - REVIEWS LIST
 * Endpoint: GET /backend/reviews/list.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$isAdmin = false;
if (preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
    $payload = JWT::decode($matches[1]);
    if ($payload && isset($payload['role']) && $payload['role'] === 'ADMIN') {
        $isAdmin = true;
    }
}

try {
    $pdo = Database::getConnection();

    $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
    $status = $_GET['status'] ?? ($isAdmin ? 'ALL' : 'APPROVED');

    $where = "WHERE 1=1";
    $params = [];

    if (!$isAdmin || $status !== 'ALL') {
        $where .= " AND r.status = ?";
        $params[] = $isAdmin ? $status : 'APPROVED';
    }

    if ($productId !== null && $productId > 0) {
        $where .= " AND r.product_id = ?";
        $params[] = $productId;
    }

    $sql = "SELECT r.*, p.name AS product_name, p.image AS product_image,
                   u.name AS user_name, u.avatar AS user_avatar
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            {$where}
            ORDER BY r.id DESC LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll();

    sendResponse(true, 'Lấy danh sách đánh giá thành công.', $reviews, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
