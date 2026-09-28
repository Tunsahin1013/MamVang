<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - UPDATE & DELETE SUPPLIER (ADMIN ONLY)
 * Endpoint: POST/PUT /backend/suppliers/update.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN']);

$input = getJsonInput();
$id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    sendResponse(false, 'Thiếu ID nhà cung cấp.', null, 400);
}

$name = trim($input['name'] ?? '');
$contactPerson = trim($input['contact_person'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');
$status = $input['status'] ?? 'ACTIVE';

if (empty($name)) {
    sendResponse(false, 'Tên nhà cung cấp không được để trống.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("UPDATE suppliers SET name = ?, contact_person = ?, email = ?, phone = ?, address = ?, status = ? WHERE id = ?");
    $stmt->execute([$name, $contactPerson, $email, $phone, $address, $status, $id]);

    sendResponse(true, 'Cập nhật nhà cung cấp thành công.', ['id' => $id], 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
