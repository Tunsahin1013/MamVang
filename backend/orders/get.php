<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - GET ORDER DETAIL
 * Endpoint: GET /backend/orders/get.php?id=1
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();

$id = (int)($_GET['id'] ?? 0);
$orderNumber = trim($_GET['order_number'] ?? '');

if ($id <= 0 && empty($orderNumber)) {
    sendResponse(false, 'Thiếu ID hoặc mã đơn hàng.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $where = $id > 0 ? "o.id = ?" : "o.order_number = ?";
    $param = $id > 0 ? $id : $orderNumber;

    $sql = "SELECT o.*, u.name AS customer_name, u.email AS customer_email,
                   c.phone AS customer_phone, c.address AS customer_address
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN customers c ON u.id = c.user_id
            WHERE {$where}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$param]);
    $order = $stmt->fetch();

    if (!$order) {
        sendResponse(false, 'Không tìm thấy đơn hàng.', null, 404);
    }

    // Role check: CUSTOMER can only view their own order
    if ($currentUser['role'] === 'CUSTOMER' && (int)$order['user_id'] !== (int)$currentUser['id']) {
        sendResponse(false, 'Bạn không có quyền xem đơn hàng của người khác.', null, 403);
    }

    $order['total_amount'] = (float)$order['total_amount'];
    $order['discount_amount'] = (float)$order['discount_amount'];
    $order['final_amount'] = (float)$order['final_amount'];

    // Get order items with image
    $stmtItems = $pdo->prepare("SELECT oi.*, p.image AS product_image, p.slug AS product_slug
                                FROM order_items oi
                                LEFT JOIN products p ON oi.product_id = p.id
                                WHERE oi.order_id = ?");
    $stmtItems->execute([$order['id']]);
    $items = $stmtItems->fetchAll();

    foreach ($items as &$item) {
        $item['price'] = (float)$item['price'];
        $item['quantity'] = (int)$item['quantity'];
        $item['subtotal'] = (float)$item['subtotal'];
    }

    $order['items'] = $items;

    // Get payment records
    $stmtPay = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
    $stmtPay->execute([$order['id']]);
    $order['payments'] = $stmtPay->fetchAll();

    sendResponse(true, 'Chi tiết đơn hàng.', $order, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
