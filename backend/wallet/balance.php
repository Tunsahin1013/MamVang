<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - GET WALLET BALANCE & TRANSACTIONS
 * Endpoint: GET /backend/wallet/balance.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();

try {
    $pdo = Database::getConnection();

    // Ensure wallet exists
    $stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
    $stmt->execute([$currentUser['id']]);
    $wallet = $stmt->fetch();

    if (!$wallet) {
        $stmtCreate = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)");
        $stmtCreate->execute([$currentUser['id']]);
        $wallet = ['id' => (int)$pdo->lastInsertId(), 'user_id' => $currentUser['id'], 'balance' => 0.00];
    }

    $wallet['balance'] = (float)$wallet['balance'];

    // Recent transactions
    $stmtTxn = $pdo->prepare("SELECT * FROM wallet_transactions WHERE wallet_id = ? ORDER BY id DESC LIMIT 20");
    $stmtTxn->execute([$wallet['id']]);
    $transactions = $stmtTxn->fetchAll();

    foreach ($transactions as &$t) {
        $t['amount'] = (float)$t['amount'];
        $t['balance_after'] = (float)$t['balance_after'];
    }

    sendResponse(true, 'Lấy thông tin ví thành công.', [
        'wallet' => $wallet,
        'transactions' => $transactions
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
