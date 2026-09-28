<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CREATE PRODUCT
 * Endpoint: POST /backend/products/create.php
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

// Admin and Employee can create products
$currentUser = AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

$input = getJsonInput();
$categoryId = (int)($input['category_id'] ?? 0);
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');
$price = (float)($input['price'] ?? 0);
$costPrice = (float)($input['cost_price'] ?? ($price * 0.6));
$stockQuantity = max(0, (int)($input['stock_quantity'] ?? 0));
$image = trim($input['image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80');
$isFeatured = !empty($input['is_featured']) ? 1 : 0;
$status = $input['status'] ?? 'AVAILABLE';

if (empty($name) || $categoryId <= 0 || $price <= 0) {
    sendResponse(false, 'Tên món ăn, danh mục và giá bán (> 0) là bắt buộc.', null, 400);
}

// Generate unique slug
$slugBase = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
if (empty($slugBase)) {
    $slugBase = 'mon-an-' . time();
}
$slug = $slugBase;

try {
    $pdo = Database::getConnection();

    // Verify category
    $stmtCat = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $stmtCat->execute([$categoryId]);
    if (!$stmtCat->fetch()) {
        sendResponse(false, 'Danh mục không tồn tại.', null, 400);
    }

    // Ensure slug unique
    $stmtSlug = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
    $stmtSlug->execute([$slug]);
    if ($stmtSlug->fetch()) {
        $slug = $slugBase . '-' . time();
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, price, cost_price, stock_quantity, image, is_featured, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$categoryId, $name, $slug, $description, $price, $costPrice, $stockQuantity, $image, $isFeatured, $status]);
    $newProductId = (int)$pdo->lastInsertId();

    // Log initial inventory transaction if stock > 0
    if ($stockQuantity > 0) {
        $stmtInv = $pdo->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, previous_stock, new_stock, reason, performed_by) 
                                  VALUES (?, 'IMPORT', ?, 0, ?, 'Nhập kho ban đầu khi tạo món ăn', ?)");
        $stmtInv->execute([$newProductId, $stockQuantity, $stockQuantity, $currentUser['id']]);
    }

    $pdo->commit();

    sendResponse(true, 'Thêm món ăn thành công!', [
        'id' => $newProductId,
        'name' => $name,
        'slug' => $slug,
        'price' => $price,
        'stock_quantity' => $stockQuantity
    ], 201);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
