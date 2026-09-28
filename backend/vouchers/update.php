<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - UPDATE & DELETE VOUCHER (ADMIN ONLY)
 * Endpoint: POST/PUT /backend/vouchers/update.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID voucher.', null, 400);
}

$description = trim($input['description'] ?? '');
$discountType = $input['discount_type'] ?? 'PERCENTAGE';
$discountValue = (float)($input['discount_value'] ?? 0);
$minOrderAmount = (float)($input['min_order_amount'] ?? 0);
$maxDiscountAmount = isset($input['max_discount_amount']) ? (float)$input['max_discount_amount'] : null;
$usageLimit = max(1, (int)($input['usage_limit'] ?? 100));
$startDate = $input['start_date'] ?? date('Y-m-d');
$endDate = $input['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
$status = $input['status'] ?? 'ACTIVE';

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("UPDATE vouchers SET description = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, max_discount_amount = ?, usage_limit = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
    $stmt->execute([$description, $discountType, $discountValue, $minOrderAmount, $maxDiscountAmount, $usageLimit, $startDate, $endDate, $status, $id]);

    sendResponse(true, 'Cập nhật voucher thành công.', ['id' => $id], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
