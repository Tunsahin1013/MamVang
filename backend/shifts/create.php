<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CREATE SHIFT DEFINITION & ASSIGN EMPLOYEE
 * Endpoint: POST /backend/shifts/create.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$name = trim($input['name'] ?? '');
$startTime = trim($input['start_time'] ?? '');
$endTime = trim($input['end_time'] ?? '');
$description = trim($input['description'] ?? '');

if (empty($name) || empty($startTime) || empty($endTime)) {
    sendResponse(false, 'Tên ca, giờ bắt đầu và giờ kết thúc là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("INSERT INTO shifts (name, start_time, end_time, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $startTime, $endTime, $description]);
    $newId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Thêm ca làm việc mới thành công.', [
        'id' => $newId,
        'name' => $name,
        'start_time' => $startTime,
        'end_time' => $endTime
    ], 201);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
