<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - UPDATE ORDER STATUS (ADMIN & EMPLOYEE)
 * Endpoint: POST /backend/orders/update_status.php
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

// Admin and Employee can update order statuses
$currentUser = AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

$input = getJsonInput();
$orderId = (int)($input['order_id'] ?? 0);
$newStatus = trim($input['status'] ?? '');
$paymentStatus = trim($input['payment_status'] ?? '');

$allowedStatuses = ['PENDING', 'CONFIRMED', 'PREPARING', 'READY', 'COMPLETED', 'CANCELLED'];
if (!in_array($newStatus, $allowedStatuses)) {
    sendResponse(false, 'Trạng thái đơn hàng không hợp lệ.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        sendResponse(false, 'Không tìm thấy đơn hàng.', null, 404);
    }

    $pdo->beginTransaction();

    // If changing to CANCELLED from an active status, restore stock
    if ($newStatus === 'CANCELLED' && $order['order_status'] !== 'CANCELLED') {
        $stmtItems = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();

        $stmtRestore = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
        $stmtInvLog = $pdo->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, previous_stock, new_stock, reason, performed_by) VALUES (?, 'ORDER_CANCEL', ?, ?, ?, ?, ?)");
        $stmtStock = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");

        foreach ($items as $item) {
            $stmtStock->execute([$item['product_id']]);
            $prev = (int)$stmtStock->fetch()['stock_quantity'];
            $new = $prev + (int)$item['quantity'];

            $stmtRestore->execute([$item['quantity'], $item['product_id']]);
            $stmtInvLog->execute([$item['product_id'], $item['quantity'], $prev, $new, "Hoàn kho do hủy đơn hàng #{$order['order_number']}", $currentUser['id']]);
        }

        // Refund to wallet if paid via WALLET
        if ($order['payment_method'] === 'WALLET' && $order['payment_status'] === 'PAID') {
            $stmtRefundWal = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
            $stmtRefundWal->execute([$order['final_amount'], $order['user_id']]);

            $stmtWalInfo = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ?");
            $stmtWalInfo->execute([$order['user_id']]);
            $walInfo = $stmtWalInfo->fetch();

            $stmtTxn = $pdo->prepare("INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, description, reference_id) VALUES (?, ?, 'REFUND', ?, ?, ?, ?)");
            $stmtTxn->execute([$walInfo['id'], $order['user_id'], $order['final_amount'], $walInfo['balance'], "Hoàn tiền đơn hàng hủy #{$order['order_number']}", $order['order_number']]);

            $paymentStatus = 'REFUNDED';
        }
    }

    // Auto mark payment as PAID if order is COMPLETED
    if ($newStatus === 'COMPLETED' && empty($paymentStatus)) {
        $paymentStatus = 'PAID';
    }

    $finalPaymentStatus = !empty($paymentStatus) ? $paymentStatus : $order['payment_status'];

    $stmtUpd = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
    $stmtUpd->execute([$newStatus, $finalPaymentStatus, $orderId]);

    // Send Notification to customer based on new status
    $statusMessages = [
        'CONFIRMED' => ['Đơn hàng đã được xác nhận', "Đơn hàng #{$order['order_number']} của bạn đã được xác nhận và chuyển cho bếp."],
        'PREPARING' => ['Đang chế biến món ăn', "Đơn hàng #{$order['order_number']} đang được các đầu bếp nấu nóng hổi."],
        'READY' => ['Món ăn đã sẵn sàng!', "Đơn hàng #{$order['order_number']} đã chuẩn bị xong. Mời bạn đến nhận món."],
        'COMPLETED' => ['Đơn hàng hoàn tất', "Đơn hàng #{$order['order_number']} đã được giao thành công. Chúc bạn ngon miệng!"],
        'CANCELLED' => ['Đơn hàng đã bị hủy', "Đơn hàng #{$order['order_number']} đã được hủy theo yêu cầu."],
    ];

    if (isset($statusMessages[$newStatus])) {
        [$title, $msg] = $statusMessages[$newStatus];
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, link) VALUES (?, ?, ?, 'ORDER', 0, ?)");
        $stmtNotif->execute([$order['user_id'], $title, $msg, "/canteen-management/frontend/customer/order-detail.html?id={$orderId}"]);
    }

    $pdo->commit();

    sendResponse(true, "Cập nhật trạng thái đơn hàng thành '{$newStatus}' thành công.", [
        'order_id' => $orderId,
        'order_status' => $newStatus,
        'payment_status' => $finalPaymentStatus
    ], 200);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cập nhật đơn hàng: ' . $e->getMessage(), null, 500);
}
