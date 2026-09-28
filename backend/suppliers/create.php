<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - CREATE SUPPLIER (ADMIN ONLY)
 * Endpoint: POST /backend/suppliers/create.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Chỉ chấp nhận phương thức POST.', null, 405);
}

$input = getJsonInput();
$name = trim($input['name'] ?? '');
$contactPerson = trim($input['contact_person'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');
$status = $input['status'] ?? 'ACTIVE';

if (empty($name)) {
    sendResponse(false, 'Tên nhà cung cấp là bắt buộc.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $contactPerson, $email, $phone, $address, $status]);
    $newId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Thêm nhà cung cấp thành công.', [
        'id' => $newId,
        'name' => $name,
        'contact_person' => $contactPerson
    ], 201);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
