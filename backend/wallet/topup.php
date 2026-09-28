<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - WALLET TOP-UP API
 * Endpoint: POST /backend/wallet/topup.php
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$currentUser = AuthMiddleware::authorize();

$input = getJsonInput();
$amount = (float)($input['amount'] ?? 0);
$method = $input['method'] ?? 'VIETQR';

if ($amount < 10000) {
    sendResponse(false, 'Số tiền nạp tối thiểu là 10.000đ.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $pdo->beginTransaction();

    // Lock wallet
    $stmt = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
    $stmt->execute([$currentUser['id']]);
    $wallet = $stmt->fetch();

    if (!$wallet) {
        $stmtCreate = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)");
        $stmtCreate->execute([$currentUser['id']]);
        $walletId = (int)$pdo->lastInsertId();
        $prevBalance = 0.00;
    } else {
        $walletId = (int)$wallet['id'];
        $prevBalance = (float)$wallet['balance'];
    }

    $newBalance = $prevBalance + $amount;

    $stmtUpd = $pdo->prepare("UPDATE wallets SET balance = ? WHERE id = ?");
    $stmtUpd->execute([$newBalance, $walletId]);

    $refId = 'TOPUP_' . date('ymdHis') . '_' . rand(100, 999);
    $desc = "Nạp tiền vào ví qua cổng {$method}";

    $stmtTxn = $pdo->prepare("INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, description, reference_id) VALUES (?, ?, 'DEPOSIT', ?, ?, ?, ?)");
    $stmtTxn->execute([$walletId, $currentUser['id'], $amount, $newBalance, $desc, $refId]);

    // Send notification
    $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, link) VALUES (?, 'Nạp tiền thành công', ?, 'WALLET', 0, '/canteen-management/frontend/customer/wallet.html')");
    $stmtNotif->execute([
        $currentUser['id'],
        "Ví căn tin đã được nạp thành công +" . number_format($amount, 0, ',', '.') . "đ. Số dư hiện tại: " . number_format($newBalance, 0, ',', '.') . "đ."
    ]);

    $pdo->commit();

    sendResponse(true, "Nạp tiền thành công +" . number_format($amount, 0, ',', '.') . "đ vào ví căn tin.", [
        'wallet_id' => $walletId,
        'amount_added' => $amount,
        'new_balance' => $newBalance,
        'reference_id' => $refId
    ], 200);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi nạp tiền ví: ' . $e->getMessage(), null, 500);
}
