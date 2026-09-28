<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - SUBMIT PRODUCT REVIEW
 * Endpoint: POST /backend/reviews/create.php
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$currentUser = AuthMiddleware::authorize();

$input = getJsonInput();
$productId = (int)($input['product_id'] ?? 0);
$rating = (int)($input['rating'] ?? 5);
$comment = trim($input['comment'] ?? '');
$orderId = isset($input['order_id']) ? (int)$input['order_id'] : null;

if ($productId <= 0 || $rating < 1 || $rating > 5) {
    sendResponse(false, 'Mã món ăn và số sao đánh giá (từ 1 đến 5 sao) là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Verify user purchased this product if CUSTOMER
    if ($currentUser['role'] === 'CUSTOMER') {
        $stmtPurchased = $pdo->prepare("SELECT o.id 
                                        FROM orders o 
                                        JOIN order_items oi ON o.id = oi.order_id 
                                        WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'COMPLETED'
                                        LIMIT 1");
        $stmtPurchased->execute([$currentUser['id'], $productId]);
        $purchase = $stmtPurchased->fetch();

        if (!$purchase) {
            sendResponse(false, 'Bạn chỉ có thể đánh giá những món ăn đã từng đặt và hoàn thành tại căn tin.', null, 403);
        }

        if ($orderId === null && $purchase) {
            $orderId = (int)$purchase['id'];
        }
    }

    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'APPROVED')");
    $stmt->execute([$productId, $currentUser['id'], $orderId, $rating, $comment]);
    $newId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Cảm ơn bạn đã gửi đánh giá món ăn! Đánh giá của bạn đã được ghi nhận.', [
        'id' => $newId,
        'rating' => $rating,
        'comment' => $comment
    ], 201);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
