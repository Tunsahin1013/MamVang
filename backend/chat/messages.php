<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - GET CHAT MESSAGES
 * Endpoint: GET /backend/chat/messages.php?chat_id=1
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();

$chatId = (int)($_GET['chat_id'] ?? 0);

try {
    $pdo = Database::getConnection();

    // If chat_id not provided and user is CUSTOMER, find their chat
    if ($chatId <= 0 && $currentUser['role'] === 'CUSTOMER') {
        $stmtChat = $pdo->prepare("SELECT id FROM chats WHERE customer_id = ?");
        $stmtChat->execute([$currentUser['id']]);
        $chatRow = $stmtChat->fetch();
        if ($chatRow) {
            $chatId = (int)$chatRow['id'];
        }
    }

    if ($chatId <= 0) {
        sendResponse(false, 'Thiếu ID cuộc trò chuyện.', null, 400);
    }

    // Verify access
    $stmtVerify = $pdo->prepare("SELECT customer_id FROM chats WHERE id = ?");
    $stmtVerify->execute([$chatId]);
    $chat = $stmtVerify->fetch();

    if (!$chat) {
        sendResponse(false, 'Không tìm thấy cuộc trò chuyện.', null, 404);
    }

    if ($currentUser['role'] === 'CUSTOMER' && (int)$chat['customer_id'] !== (int)$currentUser['id']) {
        sendResponse(false, 'Bạn không có quyền truy cập cuộc trò chuyện này.', null, 403);
    }

    // Mark messages as read
    if ($currentUser['role'] === 'CUSTOMER') {
        $stmtRead = $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE chat_id = ? AND sender_role != 'CUSTOMER'");
    } else {
        $stmtRead = $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE chat_id = ? AND sender_role = 'CUSTOMER'");
    }
    $stmtRead->execute([$chatId]);

    // Fetch messages
    $stmtMsgs = $pdo->prepare("SELECT cm.*, u.name AS sender_name, u.avatar AS sender_avatar
                               FROM chat_messages cm
                               JOIN users u ON cm.sender_id = u.id
                               WHERE cm.chat_id = ?
                               ORDER BY cm.id ASC LIMIT 100");
    $stmtMsgs->execute([$chatId]);
    $messages = $stmtMsgs->fetchAll();

    sendResponse(true, 'Lấy tin nhắn thành công.', [
        'chat_id' => $chatId,
        'messages' => $messages
    ], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
