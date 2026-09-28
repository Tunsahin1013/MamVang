<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - SEND CHAT MESSAGE
 * Endpoint: POST /backend/chat/send.php
 */

require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$currentUser = AuthMiddleware::authorize();

$input = getJsonInput();
$chatId = (int)($input['chat_id'] ?? 0);
$message = trim($input['message'] ?? '');

if (empty($message)) {
    sendResponse(false, 'Nội dung tin nhắn không được rỗng.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // If chat_id not provided and user is CUSTOMER, find or create chat
    if ($chatId <= 0 && $currentUser['role'] === 'CUSTOMER') {
        $stmtFind = $pdo->prepare("SELECT id FROM chats WHERE customer_id = ?");
        $stmtFind->execute([$currentUser['id']]);
        $chat = $stmtFind->fetch();

        if ($chat) {
            $chatId = (int)$chat['id'];
        } else {
            $stmtCreate = $pdo->prepare("INSERT INTO chats (customer_id, last_message) VALUES (?, ?)");
            $stmtCreate->execute([$currentUser['id'], $message]);
            $chatId = (int)$pdo->lastInsertId();
        }
    }

    if ($chatId <= 0) {
        sendResponse(false, 'Thiếu ID cuộc trò chuyện.', null, 400);
    }

    $pdo->beginTransaction();

    $stmtInsert = $pdo->prepare("INSERT INTO chat_messages (chat_id, sender_id, sender_role, message, is_read) VALUES (?, ?, ?, ?, 0)");
    $stmtInsert->execute([$chatId, $currentUser['id'], $currentUser['role'], $message]);
    $msgId = (int)$pdo->lastInsertId();

    // Update last message in chat
    if ($currentUser['role'] !== 'CUSTOMER') {
        $stmtUpd = $pdo->prepare("UPDATE chats SET last_message = ?, employee_id = ?, updated_at = NOW() WHERE id = ?");
        $stmtUpd->execute([$message, $currentUser['id'], $chatId]);
    } else {
        $stmtUpd = $pdo->prepare("UPDATE chats SET last_message = ?, updated_at = NOW() WHERE id = ?");
        $stmtUpd->execute([$message, $chatId]);
    }

    $pdo->commit();

    sendResponse(true, 'Gửi tin nhắn thành công.', [
        'id' => $msgId,
        'chat_id' => $chatId,
        'sender_id' => $currentUser['id'],
        'sender_role' => $currentUser['role'],
        'sender_name' => $currentUser['name'],
        'message' => $message,
        'created_at' => date('Y-m-d H:i:s')
    ], 201);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
