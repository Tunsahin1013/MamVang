<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CREATE VOUCHER (ADMIN ONLY)
 * Endpoint: POST /backend/vouchers/create.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$code = strtoupper(trim($input['code'] ?? ''));
$description = trim($input['description'] ?? '');
$discountType = $input['discount_type'] ?? 'PERCENTAGE';
$discountValue = (float)($input['discount_value'] ?? 0);
$minOrderAmount = (float)($input['min_order_amount'] ?? 0);
$maxDiscountAmount = isset($input['max_discount_amount']) ? (float)$input['max_discount_amount'] : null;
$usageLimit = max(1, (int)($input['usage_limit'] ?? 100));
$startDate = $input['start_date'] ?? date('Y-m-d');
$endDate = $input['end_date'] ?? date('Y-m-d', strtotime('+30 days'));

if (empty($code) || $discountValue <= 0) {
    sendResponse(false, 'Mã voucher và giá trị giảm (> 0) là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmtCheck = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
    $stmtCheck->execute([$code]);
    if ($stmtCheck->fetch()) {
        sendResponse(false, 'Mã giảm giá này đã tồn tại.', null, 409);
    }

    $stmt = $pdo->prepare("INSERT INTO vouchers 
        (code, description, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, start_date, end_date, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE')");
    $stmt->execute([$code, $description, $discountType, $discountValue, $minOrderAmount, $maxDiscountAmount, $usageLimit, $startDate, $endDate]);
    $newId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Tạo mã voucher thành công.', ['id' => $newId, 'code' => $code], 201);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
