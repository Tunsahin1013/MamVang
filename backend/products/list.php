<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - PRODUCTS LIST & SEARCH API
 * Endpoint: GET /backend/products/list.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();

    $categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
    $search = trim($_GET['search'] ?? '');
    $status = $_GET['status'] ?? 'AVAILABLE';
    $featured = isset($_GET['featured']) ? (int)$_GET['featured'] : null;
    $sort = $_GET['sort'] ?? 'new';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $where = "WHERE 1=1";
    $params = [];

    if ($categoryId !== null && $categoryId > 0) {
        $where .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }

    if (!empty($search)) {
        $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    if ($status !== 'ALL') {
        $where .= " AND p.status = ?";
        $params[] = $status;
    }

    if ($featured !== null) {
        $where .= " AND p.is_featured = ?";
        $params[] = $featured;
    }

    // Sorting
    $orderBy = match ($sort) {
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name' => 'p.name ASC',
        'bestseller' => 'order_count DESC',
        default => 'p.id DESC',
    };

    // Count total query
    $countSql = "SELECT COUNT(*) as total FROM products p {$where}";
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetch()['total'];

    // Main query with category name, avg rating, and order count
    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   COALESCE(AVG(r.rating), 5.0) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count,
                   COALESCE(SUM(oi.quantity), 0) AS order_count
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'APPROVED'
            LEFT JOIN order_items oi ON p.id = oi.product_id
            {$where}
            GROUP BY p.id
            ORDER BY {$orderBy}
            LIMIT {$limit} OFFSET {$offset}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Format fields
    foreach ($products as &$p) {
        $p['price'] = (float)$p['price'];
        $p['cost_price'] = (float)$p['cost_price'];
        $p['stock_quantity'] = (int)$p['stock_quantity'];
        $p['avg_rating'] = round((float)$p['avg_rating'], 1);
        $p['review_count'] = (int)$p['review_count'];
        $p['order_count'] = (int)$p['order_count'];
        $p['is_featured'] = (bool)$p['is_featured'];
    }

    sendResponse(true, 'Lấy danh sách món ăn thành công.', [
        'items' => $products,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
