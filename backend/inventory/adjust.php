<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - ADJUST STOCK INVENTORY
 * Endpoint: POST /backend/inventory/adjust.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$productId = (int)($input['product_id'] ?? 0);
$actualStock = max(0, (int)($input['actual_stock'] ?? 0));
$reason = trim($input['reason'] ?? 'Kiểm kê định kỳ thực tế');

if ($productId <= 0) {
    sendResponse(false, 'Mã món ăn không hợp lệ.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT id, name, stock_quantity FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        sendResponse(false, 'Món ăn không tồn tại.', null, 404);
    }

    $pdo->beginTransaction();

    $prev = (int)$product['stock_quantity'];
    $diff = $actualStock - $prev;

    $newStatus = $actualStock <= 0 ? 'OUT_OF_STOCK' : 'AVAILABLE';
    $stmtUpd = $pdo->prepare("UPDATE products SET stock_quantity = ?, status = ? WHERE id = ?");
    $stmtUpd->execute([$actualStock, $newStatus, $productId]);

    $stmtLog = $pdo->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, previous_stock, new_stock, reason, performed_by) VALUES (?, 'ADJUSTMENT', ?, ?, ?, ?, ?)");
    $stmtLog->execute([$productId, abs($diff), $prev, $actualStock, "{$reason} (Chênh lệch: " . ($diff >= 0 ? "+{$diff}" : $diff) . ")", $currentUser['id']]);

    $pdo->commit();

    sendResponse(true, "Điều chỉnh tồn kho món \"{$product['name']}\" thành công ({$prev} -> {$actualStock}).", [
        'product_id' => $productId,
        'previous_stock' => $prev,
        'actual_stock' => $actualStock,
        'difference' => $diff
    ], 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
