<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - WALLET TRANSACTIONS LIST
 * Endpoint: GET /backend/wallet/transactions.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();

try {
    $pdo = Database::getConnection();

    $where = "WHERE 1=1";
    $params = [];

    if ($currentUser['role'] === 'CUSTOMER') {
        $where .= " AND wt.user_id = ?";
        $params[] = $currentUser['id'];
    }

    $sql = "SELECT wt.*, u.name AS user_name, u.email AS user_email
            FROM wallet_transactions wt
            JOIN users u ON wt.user_id = u.id
            {$where}
            ORDER BY wt.id DESC LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $txns = $stmt->fetchAll();

    foreach ($txns as &$t) {
        $t['amount'] = (float)$t['amount'];
        $t['balance_after'] = (float)$t['balance_after'];
    }

    sendResponse(true, 'Lấy lịch sử giao dịch ví thành công.', $txns, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
