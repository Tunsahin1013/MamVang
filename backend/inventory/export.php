<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - EXPORT STOCK (DAMAGE/WASTE/INTERNAL USE)
 * Endpoint: POST /backend/inventory/export.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$productId = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 0);
$reason = trim($input['reason'] ?? 'Xuất hủy / Hết hạn sử dụng');

if ($productId <= 0 || $quantity <= 0) {
    sendResponse(false, 'Mã món ăn và số lượng xuất (> 0) là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmtCheck = $pdo->prepare("SELECT id, name, stock_quantity FROM products WHERE id = ? FOR UPDATE");
    $stmtCheck->execute([$productId]);
    $product = $stmtCheck->fetch();

    if (!$product) {
        sendResponse(false, 'Món ăn không tồn tại.', null, 404);
    }

    $prevStock = (int)$product['stock_quantity'];
    if ($prevStock < $quantity) {
        sendResponse(false, "Số lượng trong kho ({$prevStock}) không đủ để xuất ({$quantity}).", null, 400);
    }

    $pdo->beginTransaction();

    $newStock = $prevStock - $quantity;
    $newStatus = $newStock <= 0 ? 'OUT_OF_STOCK' : 'AVAILABLE';

    $stmtUpd = $pdo->prepare("UPDATE products SET stock_quantity = ?, status = ? WHERE id = ?");
    $stmtUpd->execute([$newStock, $newStatus, $productId]);

    $stmtLog = $pdo->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, previous_stock, new_stock, reason, performed_by) VALUES (?, 'EXPORT', ?, ?, ?, ?, ?)");
    $stmtLog->execute([$productId, $quantity, $prevStock, $newStock, $reason, $currentUser['id']]);

    $pdo->commit();

    sendResponse(true, "Xuất kho thành công -{$quantity} phần cho món \"{$product['name']}\". Tồn kho còn lại: {$newStock}.", [
        'product_id' => $productId,
        'previous_stock' => $prevStock,
        'quantity_exported' => $quantity,
        'new_stock' => $newStock
    ], 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
