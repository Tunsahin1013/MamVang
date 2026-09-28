<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - INVENTORY LIST & STOCK SUMMARY
 * Endpoint: GET /backend/inventory/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

try {
    $pdo = Database::getConnection();

    $threshold = (int)($_GET['threshold'] ?? 10);
    $filter = $_GET['filter'] ?? 'ALL'; // ALL, LOW_STOCK, OUT_OF_STOCK
    $search = trim($_GET['search'] ?? '');

    $where = "WHERE p.status != 'DISCONTINUED'";
    $params = [];

    if ($filter === 'LOW_STOCK') {
        $where .= " AND p.stock_quantity <= ? AND p.stock_quantity > 0";
        $params[] = $threshold;
    } elseif ($filter === 'OUT_OF_STOCK') {
        $where .= " AND p.stock_quantity <= 0";
    }

    if (!empty($search)) {
        $where .= " AND (p.name LIKE ? OR c.name LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $sql = "SELECT p.id, p.name, p.slug, p.price, p.cost_price, p.stock_quantity, p.image, p.status,
                   c.name AS category_name,
                   (p.stock_quantity * p.cost_price) AS total_inventory_value,
                   CASE 
                       WHEN p.stock_quantity <= 0 THEN 'OUT_OF_STOCK'
                       WHEN p.stock_quantity <= ? THEN 'LOW_STOCK'
                       ELSE 'IN_STOCK'
                   END AS stock_alert_level
            FROM products p
            JOIN categories c ON p.category_id = c.id
            {$where}
            ORDER BY p.stock_quantity ASC, p.id DESC";

    // Add threshold to beginning of params for CASE condition
    array_unshift($params, $threshold);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inventory = $stmt->fetchAll();

    $totalValuation = 0.00;
    $lowStockCount = 0;
    $outOfStockCount = 0;

    foreach ($inventory as &$item) {
        $item['price'] = (float)$item['price'];
        $item['cost_price'] = (float)$item['cost_price'];
        $item['stock_quantity'] = (int)$item['stock_quantity'];
        $item['total_inventory_value'] = (float)$item['total_inventory_value'];
        $totalValuation += $item['total_inventory_value'];

        if ($item['stock_alert_level'] === 'OUT_OF_STOCK') {
            $outOfStockCount++;
        } elseif ($item['stock_alert_level'] === 'LOW_STOCK') {
            $lowStockCount++;
        }
    }

    // Recent 10 inventory transactions
    $stmtTxn = $pdo->query("SELECT it.*, p.name AS product_name, u.name AS performed_by_name 
                            FROM inventory_transactions it
                            JOIN products p ON it.product_id = p.id
                            JOIN users u ON it.performed_by = u.id
                            ORDER BY it.id DESC LIMIT 15");
    $recentTransactions = $stmtTxn->fetchAll();

    sendResponse(true, 'Lấy dữ liệu tồn kho thành công.', [
        'items' => $inventory,
        'summary' => [
            'total_items' => count($inventory),
            'total_valuation' => $totalValuation,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount
        ],
        'recent_transactions' => $recentTransactions
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
