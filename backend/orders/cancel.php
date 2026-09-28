<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CANCEL ORDER
 * Endpoint: POST /backend/orders/cancel.php
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$currentUser = AuthMiddleware::authorize();
$input = getJsonInput();
$orderId = (int)($input['order_id'] ?? 0);
$reason = trim($input['reason'] ?? 'Sinh Viên hủy đơn');

if ($orderId <= 0) {
    sendResponse(false, 'Thiếu ID đơn hàng cần hủy.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        sendResponse(false, 'Không tìm thấy đơn hàng.', null, 404);
    }

    if ($order['order_status'] === 'CANCELLED') {
        sendResponse(false, 'Đơn hàng này đã bị hủy trước đó.', null, 400);
    }

    if ($order['order_status'] === 'COMPLETED') {
        sendResponse(false, 'Đơn hàng đã hoàn thành và nhận món, không thể hủy.', null, 400);
    }

    // Customer can only cancel their own order and only while it is PENDING
    if ($currentUser['role'] === 'CUSTOMER') {
        if ((int)$order['user_id'] !== (int)$currentUser['id']) {
            sendResponse(false, 'Bạn không thể hủy đơn hàng của người khác.', null, 403);
        }
        if ($order['order_status'] !== 'PENDING') {
            sendResponse(false, 'Đơn hàng đã được xác nhận hoặc đang nấu, vui lòng liên hệ nhân viên nếu cần hỗ trợ.', null, 400);
        }
    }

    $pdo->beginTransaction();

    // 1. Restore product stock
    $stmtItems = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll();

    $stmtRestore = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
    $stmtStock = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stmtInvLog = $pdo->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, previous_stock, new_stock, reason, performed_by) VALUES (?, 'ORDER_CANCEL', ?, ?, ?, ?, ?)");

    foreach ($items as $item) {
        $stmtStock->execute([$item['product_id']]);
        $prev = (int)$stmtStock->fetch()['stock_quantity'];
        $new = $prev + (int)$item['quantity'];

        $stmtRestore->execute([$item['quantity'], $item['product_id']]);
        $stmtInvLog->execute([$item['product_id'], $item['quantity'], $prev, $new, "Khôi phục kho do hủy đơn #{$order['order_number']}: {$reason}", $currentUser['id']]);
    }

    // 2. Refund wallet if applicable
    $newPaymentStatus = $order['payment_status'];
    if ($order['payment_method'] === 'WALLET' && $order['payment_status'] === 'PAID') {
        $stmtRefund = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
        $stmtRefund->execute([$order['final_amount'], $order['user_id']]);

        $stmtWal = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ?");
        $stmtWal->execute([$order['user_id']]);
        $wal = $stmtWal->fetch();

        $stmtTxn = $pdo->prepare("INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, description, reference_id) VALUES (?, ?, 'REFUND', ?, ?, ?, ?)");
        $stmtTxn->execute([$wal['id'], $order['user_id'], $order['final_amount'], $wal['balance'], "Hoàn tiền hủy đơn #{$order['order_number']}", $order['order_number']]);

        $newPaymentStatus = 'REFUNDED';
    }

    // 3. Update order status
    $stmtUpd = $pdo->prepare("UPDATE orders SET order_status = 'CANCELLED', payment_status = ? WHERE id = ?");
    $stmtUpd->execute([$newPaymentStatus, $orderId]);

    // 4. Send notification
    $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, link) VALUES (?, 'Đơn hàng đã hủy', ?, 'ORDER', 0, ?)");
    $stmtNotif->execute([
        $order['user_id'],
        "Đơn hàng #{$order['order_number']} đã được hủy. Lý do: {$reason}",
        "/canteen-management/frontend/customer/order-detail.html?id={$orderId}"
    ]);

    $pdo->commit();

    sendResponse(true, 'Hủy đơn hàng thành công!', [
        'order_id' => $orderId,
        'order_status' => 'CANCELLED',
        'payment_status' => $newPaymentStatus
    ], 200);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi khi hủy đơn hàng: ' . $e->getMessage(), null, 500);
}
