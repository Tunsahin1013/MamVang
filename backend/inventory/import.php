<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - IMPORT STOCK API
 * Endpoint: POST /backend/inventory/import.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$productId = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 0);
$costPrice = isset($input['cost_price']) ? (float)$input['cost_price'] : null;
$supplierId = isset($input['supplier_id']) ? (int)$input['supplier_id'] : null;
$reason = trim($input['reason'] ?? 'Nhập hàng từ nhà cung cấp');

if ($productId <= 0 || $quantity <= 0) {
    sendResponse(false, 'Mã món ăn và số lượng nhập (> 0) là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmtCheck = $pdo->prepare("SELECT id, name, stock_quantity, status FROM products WHERE id = ? FOR UPDATE");
    $stmtCheck->execute([$productId]);
    $product = $stmtCheck->fetch();

    if (!$product) {
        sendResponse(false, 'Món ăn không tồn tại.', null, 404);
    }

    $pdo->beginTransaction();

    $prevStock = (int)$product['stock_quantity'];
    $newStock = $prevStock + $quantity;

    // Update stock and cost price if provided
    if ($costPrice !== null && $costPrice > 0) {
        $stmtUpd = $pdo->prepare("UPDATE products SET stock_quantity = ?, cost_price = ?, status = 'AVAILABLE' WHERE id = ?");
        $stmtUpd->execute([$newStock, $costPrice, $productId]);
    } else {
        $stmtUpd = $pdo->prepare("UPDATE products SET stock_quantity = ?, status = 'AVAILABLE' WHERE id = ?");
        $stmtUpd->execute([$newStock, $productId]);
    }

    // Log transaction
    $stmtLog = $pdo->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, previous_stock, new_stock, reason, performed_by) VALUES (?, 'IMPORT', ?, ?, ?, ?, ?)");
    $stmtLog->execute([$productId, $quantity, $prevStock, $newStock, $reason, $currentUser['id']]);

    $pdo->commit();

    sendResponse(true, "Nhập kho thành công +{$quantity} phần cho món \"{$product['name']}\". Tồn kho hiện tại: {$newStock}.", [
        'product_id' => $productId,
        'previous_stock' => $prevStock,
        'quantity_added' => $quantity,
        'new_stock' => $newStock
    ], 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
