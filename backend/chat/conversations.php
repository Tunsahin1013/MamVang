<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CHAT CONVERSATIONS
 * Endpoint: GET /backend/chat/conversations.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize();

try {
    $pdo = Database::getConnection();

    if ($currentUser['role'] === 'CUSTOMER') {
        // Customer finds or creates their chat
        $stmt = $pdo->prepare("SELECT c.*, u.name AS employee_name, u.avatar AS employee_avatar
                               FROM chats c
                               LEFT JOIN users u ON c.employee_id = u.id
                               WHERE c.customer_id = ?");
        $stmt->execute([$currentUser['id']]);
        $chat = $stmt->fetch();

        if (!$chat) {
            $stmtCreate = $pdo->prepare("INSERT INTO chats (customer_id, last_message) VALUES (?, 'Bắt đầu cuộc trò chuyện với căn tin')");
            $stmtCreate->execute([$currentUser['id']]);
            $chatId = (int)$pdo->lastInsertId();
            $chat = ['id' => $chatId, 'customer_id' => $currentUser['id'], 'employee_id' => null, 'last_message' => ''];
        }

        sendResponse(true, 'Cuộc trò chuyện của bạn.', [$chat], 200);

    } else {
        // Admin or Employee: list all customer conversations
        $sql = "SELECT c.*, u.name AS customer_name, u.avatar AS customer_avatar, u.email AS customer_email,
                       (SELECT COUNT(*) FROM chat_messages cm WHERE cm.chat_id = c.id AND cm.sender_role = 'CUSTOMER' AND cm.is_read = 0) AS unread_customer_messages
                FROM chats c
                JOIN users u ON c.customer_id = u.id
                ORDER BY c.updated_at DESC LIMIT 30";
        $stmt = $pdo->query($sql);
        $conversations = $stmt->fetchAll();

        sendResponse(true, 'Danh sách cuộc trò chuyện hỗ trợ.', $conversations, 200);
    }

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
