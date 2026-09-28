<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CREATE ORDER WITH TRANSACTION
 * Endpoint: POST /backend/orders/create.php
 * Strictly validates stock and calculates prices server-side from database!
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

// Any authenticated user can place an order (Customer, Admin, Employee)
$currentUser = AuthMiddleware::authorize();

$input = getJsonInput();
$items = $input['items'] ?? [];
$voucherCode = trim($input['voucher_code'] ?? '');
$paymentMethod = $input['payment_method'] ?? 'CASH';
$tableNumber = trim($input['table_number'] ?? 'Mang đi');
$notes = trim($input['notes'] ?? '');

if (empty($items) || !is_array($items)) {
    sendResponse(false, 'Giỏ hàng trống. Vui lòng chọn ít nhất một món ăn.', null, 400);
}

if (!in_array($paymentMethod, ['CASH', 'WALLET', 'BANK_TRANSFER'])) {
    $paymentMethod = 'CASH';
}

try {
    $pdo = Database::getConnection();

    // 1. BEGIN TRANSACTION
    $pdo->beginTransaction();

    $subtotal = 0.00;
    $preparedItems = [];

    // 2. Fetch fresh product details, verify stock, and compute subtotal securely from DB
    $stmtProd = $pdo->prepare("SELECT id, name, price, stock_quantity, status FROM products WHERE id = ? FOR UPDATE");

    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);
        $itemNotes = trim($item['notes'] ?? '');

        if ($productId <= 0 || $quantity <= 0) {
            $pdo->rollBack();
            sendResponse(false, 'Món ăn hoặc số lượng không hợp lệ.', null, 400);
        }

        $stmtProd->execute([$productId]);
        $product = $stmtProd->fetch();

        if (!$product) {
            $pdo->rollBack();
            sendResponse(false, "Món ăn ID #{$productId} không còn tồn tại.", null, 404);
        }

        if ($product['status'] !== 'AVAILABLE') {
            $pdo->rollBack();
            sendResponse(false, "Món \"{$product['name']}\" hiện đang tạm ngưng phục vụ.", null, 400);
        }

        if ($product['stock_quantity'] < $quantity) {
            $pdo->rollBack();
            sendResponse(false, "Món \"{$product['name']}\" chỉ còn {$product['stock_quantity']} phần trong kho, không đủ số lượng bạn yêu cầu ({$quantity} phần).", null, 400);
        }

        $price = (float)$product['price'];
        $itemSubtotal = $price * $quantity;
        $subtotal += $itemSubtotal;

        $preparedItems[] = [
            'product_id' => $productId,
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $itemSubtotal,
            'notes' => $itemNotes,
            'current_stock' => (int)$product['stock_quantity']
        ];
    }

    // 3. Process Voucher if applied
    $discountAmount = 0.00;
    $appliedVoucher = null;

    if (!empty($voucherCode)) {
        $stmtVoucher = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND status = 'ACTIVE' FOR UPDATE");
        $stmtVoucher->execute([$voucherCode]);
        $voucher = $stmtVoucher->fetch();

        $today = date('Y-m-d');
        if ($voucher) {
            if ($today >= $voucher['start_date'] && $today <= $voucher['end_date']) {
                if ($voucher['times_used'] < $voucher['usage_limit']) {
                    if ($subtotal >= (float)$voucher['min_order_amount']) {
                        if ($voucher['discount_type'] === 'PERCENTAGE') {
                            $discountAmount = ($subtotal * (float)$voucher['discount_value']) / 100.0;
                            if (!empty($voucher['max_discount_amount']) && (float)$voucher['max_discount_amount'] > 0) {
                                $discountAmount = min($discountAmount, (float)$voucher['max_discount_amount']);
                            }
                        } else {
                            $discountAmount = min((float)$voucher['discount_value'], $subtotal);
                        }
                        $appliedVoucher = $voucher;

                        // Increment voucher usage
                        $stmtUpdVoucher = $pdo->prepare("UPDATE vouchers SET times_used = times_used + 1 WHERE id = ?");
                        $stmtUpdVoucher->execute([$voucher['id']]);
                    }
                }
            }
        }
    }

    $finalAmount = max(0.00, $subtotal - $discountAmount);

    // 4. Handle Wallet Payment
    $paymentStatus = 'PENDING';
    if ($paymentMethod === 'WALLET') {
        // Fetch and lock wallet
        $stmtWallet = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
        $stmtWallet->execute([$currentUser['id']]);
        $wallet = $stmtWallet->fetch();

        if (!$wallet || (float)$wallet['balance'] < $finalAmount) {
            $pdo->rollBack();
            $balance = $wallet ? (float)$wallet['balance'] : 0.00;
            sendResponse(false, "Số dư ví căn tin không đủ để thanh toán (Hiện có: " . number_format($balance, 0, ',', '.') . "đ, Cần: " . number_format($finalAmount, 0, ',', '.') . "đ). Vui lòng nạp thêm tiền hoặc chọn thanh toán tiền mặt.", null, 400);
        }

        $newBalance = (float)$wallet['balance'] - $finalAmount;
        $stmtDeduct = $pdo->prepare("UPDATE wallets SET balance = ? WHERE id = ?");
        $stmtDeduct->execute([$newBalance, $wallet['id']]);

        $paymentStatus = 'PAID';
    } elseif ($paymentMethod === 'BANK_TRANSFER') {
        $paymentStatus = 'PAID'; // Simulated successful instant QR payment
    }

    // 5. Generate unique Order Number
    $orderNumber = 'CT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

    // Get customer id if exists
    $stmtCust = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
    $stmtCust->execute([$currentUser['id']]);
    $cust = $stmtCust->fetch();
    $customerId = $cust ? (int)$cust['id'] : null;

    // 6. Insert Order
    $stmtOrder = $pdo->prepare("INSERT INTO orders 
        (order_number, user_id, customer_id, total_amount, discount_amount, final_amount, voucher_code, payment_method, payment_status, order_status, table_number, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', ?, ?)");
    $stmtOrder->execute([
        $orderNumber,
        $currentUser['id'],
        $customerId,
        $subtotal,
        $discountAmount,
        $finalAmount,
        $appliedVoucher ? $voucherCode : null,
        $paymentMethod,
        $paymentStatus,
        $tableNumber,
        $notes
    ]);
    $orderId = (int)$pdo->lastInsertId();

    // 7. Insert Order Items & Update Product Inventory
    $stmtOrderItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtDecStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
    $stmtInvLog = $pdo->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, previous_stock, new_stock, reason, performed_by) VALUES (?, 'ORDER_SALE', ?, ?, ?, ?, ?)");

    foreach ($preparedItems as $item) {
        $stmtOrderItem->execute([
            $orderId,
            $item['product_id'],
            $item['name'],
            $item['price'],
            $item['quantity'],
            $item['subtotal'],
            $item['notes']
        ]);

        // Decrement stock
        $stmtDecStock->execute([$item['quantity'], $item['product_id']]);

        // Log transaction
        $newStock = $item['current_stock'] - $item['quantity'];
        $stmtInvLog->execute([
            $item['product_id'],
            $item['quantity'],
            $item['current_stock'],
            $newStock,
            "Xuất bán đơn hàng #{$orderNumber}",
            $currentUser['id']
        ]);
    }

    // 8. Record Payment log
    $stmtPayment = $pdo->prepare("INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtPayment->execute([
        $orderId,
        $currentUser['id'],
        $finalAmount,
        $paymentMethod,
        'TXN_' . $orderNumber,
        $paymentStatus === 'PAID' ? 'COMPLETED' : 'PENDING'
    ]);

    // 9. If paid via wallet, log wallet transaction
    if ($paymentMethod === 'WALLET' && isset($wallet)) {
        $stmtWalTxn = $pdo->prepare("INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, description, reference_id) VALUES (?, ?, 'PAYMENT', ?, ?, ?, ?)");
        $stmtWalTxn->execute([
            $wallet['id'],
            $currentUser['id'],
            $finalAmount,
            $newBalance,
            "Thanh toán đơn hàng #{$orderNumber} tại Căn Tin",
            $orderNumber
        ]);
    }

    // 10. Award Loyalty points (1 point per 10,000 VND)
    $earnedPoints = (int)floor($finalAmount / 10000);
    if ($earnedPoints > 0 && $customerId) {
        $stmtPoints = $pdo->prepare("UPDATE customers SET points = points + ? WHERE id = ?");
        $stmtPoints->execute([$earnedPoints, $customerId]);
    }

    // 11. Send System & User Notification
    $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, link) VALUES (?, ?, ?, 'ORDER', 0, ?)");
    $stmtNotif->execute([
        $currentUser['id'],
        'Đặt đơn thành công!',
        "Đơn hàng #{$orderNumber} trị giá " . number_format($finalAmount, 0, ',', '.') . "đ đã được gửi đến nhà bếp. Bạn được tích lũy +{$earnedPoints} điểm.",
        "/canteen-management/frontend/customer/order-detail.html?id={$orderId}"
    ]);

    // 12. COMMIT TRANSACTION
    $pdo->commit();

    sendResponse(true, 'Đặt món thành công! Căn tin đã tiếp nhận đơn hàng của bạn.', [
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'final_amount' => $finalAmount,
        'payment_status' => $paymentStatus,
        'earned_points' => $earnedPoints
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Đặt món thất bại do lỗi giao dịch: ' . $e->getMessage(), null, 500);
}
