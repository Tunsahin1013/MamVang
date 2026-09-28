<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - UPDATE PRODUCT
 * Endpoint: POST/PUT /backend/products/update.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID món ăn cần cập nhật.', null, 400);
}

$categoryId = (int)($input['category_id'] ?? 0);
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');
$price = (float)($input['price'] ?? 0);
$costPrice = (float)($input['cost_price'] ?? 0);
$stockQuantity = isset($input['stock_quantity']) ? (int)$input['stock_quantity'] : null;
$image = trim($input['image'] ?? '');
$isFeatured = isset($input['is_featured']) ? (!empty($input['is_featured']) ? 1 : 0) : null;
$status = $input['status'] ?? null;

if (empty($name) || $categoryId <= 0 || $price <= 0) {
    sendResponse(false, 'Tên món ăn, danh mục và đơn giá (> 0) là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Check product exists
    $stmtCheck = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmtCheck->execute([$id]);
    $currentProduct = $stmtCheck->fetch();

    if (!$currentProduct) {
        sendResponse(false, 'Món ăn không tồn tại.', null, 404);
    }

    $pdo->beginTransaction();

    // Check if stock quantity changed to log adjustment
    if ($stockQuantity !== null && $stockQuantity !== (int)$currentProduct['stock_quantity']) {
        $diff = $stockQuantity - (int)$currentProduct['stock_quantity'];
        $type = $diff > 0 ? 'IMPORT' : 'EXPORT';
        $stmtInv = $pdo->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, previous_stock, new_stock, reason, performed_by) 
                                  VALUES (?, ?, ?, ?, ?, 'Điều chỉnh tồn kho từ trang quản lý món ăn', ?)");
        $stmtInv->execute([$id, $type, abs($diff), $currentProduct['stock_quantity'], $stockQuantity, $currentUser['id']]);
    } else {
        $stockQuantity = (int)$currentProduct['stock_quantity'];
    }

    $finalStatus = $status ?: $currentProduct['status'];
    $finalFeatured = $isFeatured !== null ? $isFeatured : (int)$currentProduct['is_featured'];
    $finalImage = !empty($image) ? $image : $currentProduct['image'];

    $stmtUpdate = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, cost_price = ?, stock_quantity = ?, image = ?, is_featured = ?, status = ? WHERE id = ?");
    $stmtUpdate->execute([$categoryId, $name, $description, $price, $costPrice, $stockQuantity, $finalImage, $finalFeatured, $finalStatus, $id]);

    $pdo->commit();

    sendResponse(true, 'Cập nhật món ăn thành công.', [
        'id' => $id,
        'name' => $name,
        'price' => $price,
        'stock_quantity' => $stockQuantity,
        'status' => $finalStatus
    ], 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cập nhật: ' . $e->getMessage(), null, 500);
}
