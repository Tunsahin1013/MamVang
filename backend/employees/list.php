<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - EMPLOYEES LIST (ADMIN ONLY)
 * Endpoint: GET /backend/employees/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

AuthMiddleware::authorize(['ADMIN', 'EMPLOYEE']);

try {
    $pdo = Database::getConnection();

    $department = $_GET['department'] ?? 'ALL';
    $search = trim($_GET['search'] ?? '');

    $where = "WHERE u.role = 'EMPLOYEE'";
    $params = [];

    if ($department !== 'ALL') {
        $where .= " AND e.department = ?";
        $params[] = $department;
    }

    if (!empty($search)) {
        $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR e.position LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $sql = "SELECT e.id AS employee_id, u.id AS user_id, u.name, u.email, u.avatar, u.status AS user_status,
                   e.phone, e.department, e.position, e.salary, e.hire_date, e.status AS employee_status,
                   (SELECT COUNT(*) FROM attendance a WHERE a.employee_id = e.id) AS total_shifts_worked
            FROM employees e
            JOIN users u ON e.user_id = u.id
            {$where}
            ORDER BY e.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $employees = $stmt->fetchAll();

    foreach ($employees as &$emp) {
        $emp['salary'] = (float)$emp['salary'];
        $emp['total_shifts_worked'] = (int)$emp['total_shifts_worked'];
    }

    sendResponse(true, 'Lấy danh sách nhân viên thành công.', $employees, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
