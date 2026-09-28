<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - VALIDATE & CALCULATE VOUCHER
 * Endpoint: POST /backend/vouchers/validate.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$code = strtoupper(trim($input['code'] ?? ''));
$subtotal = (float)($input['subtotal'] ?? 0);

if (empty($code)) {
    sendResponse(false, 'Vui lòng nhập mã giảm giá.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ?");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch();

    if (!$voucher) {
        sendResponse(false, 'Mã giảm giá không tồn tại hoặc đã bị xóa.', null, 404);
    }

    if ($voucher['status'] !== 'ACTIVE') {
        sendResponse(false, 'Mã giảm giá này hiện không còn hoạt động.', null, 400);
    }

    $today = date('Y-m-d');
    if ($today < $voucher['start_date']) {
        sendResponse(false, 'Mã giảm giá chưa đến ngày áp dụng.', null, 400);
    }

    if ($today > $voucher['end_date']) {
        sendResponse(false, 'Mã giảm giá đã hết hạn sử dụng.', null, 400);
    }

    if ((int)$voucher['times_used'] >= (int)$voucher['usage_limit']) {
        sendResponse(false, 'Mã giảm giá đã hết lượt sử dụng.', null, 400);
    }

    $minOrder = (float)$voucher['min_order_amount'];
    if ($subtotal < $minOrder) {
        sendResponse(false, "Đơn hàng tối thiểu để áp dụng mã này là " . number_format($minOrder, 0, ',', '.') . "đ (Đơn hiện tại: " . number_format($subtotal, 0, ',', '.') . "đ).", null, 400);
    }

    // Calculate discount
    $discountAmount = 0.00;
    if ($voucher['discount_type'] === 'PERCENTAGE') {
        $discountAmount = ($subtotal * (float)$voucher['discount_value']) / 100.0;
        if (!empty($voucher['max_discount_amount']) && (float)$voucher['max_discount_amount'] > 0) {
            $discountAmount = min($discountAmount, (float)$voucher['max_discount_amount']);
        }
    } else {
        $discountAmount = min((float)$voucher['discount_value'], $subtotal);
    }

    $finalAmount = max(0.00, $subtotal - $discountAmount);

    sendResponse(true, "Áp dụng mã giảm giá thành công! Giảm " . number_format($discountAmount, 0, ',', '.') . "đ.", [
        'code' => $voucher['code'],
        'description' => $voucher['description'],
        'discount_type' => $voucher['discount_type'],
        'discount_value' => (float)$voucher['discount_value'],
        'discount_amount' => $discountAmount,
        'subtotal' => $subtotal,
        'final_amount' => $finalAmount
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
