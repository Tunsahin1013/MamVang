<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - ATTENDANCE LIST & HISTORY
 * Endpoint: GET /backend/attendance/list.php
 */

require_once __DIR__ . '/../middleware/auth.php';

$currentUser = AuthMiddleware::authorize(['EMPLOYEE', 'ADMIN']);

try {
    $pdo = Database::getConnection();

    $date = trim($_GET['date'] ?? '');
    $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : null;

    $where = "WHERE 1=1";
    $params = [];

    // If Employee, they can only view their own attendance unless Admin
    if ($currentUser['role'] === 'EMPLOYEE') {
        $stmtEmp = $pdo->prepare("SELECT id FROM employees WHERE user_id = ?");
        $stmtEmp->execute([$currentUser['id']]);
        $emp = $stmtEmp->fetch();
        $myEmpId = $emp ? (int)$emp['id'] : 0;
        $where .= " AND a.employee_id = ?";
        $params[] = $myEmpId;
    } elseif ($employeeId !== null && $employeeId > 0) {
        $where .= " AND a.employee_id = ?";
        $params[] = $employeeId;
    }

    if (!empty($date)) {
        $where .= " AND DATE(a.check_in_time) = ?";
        $params[] = $date;
    }

    $sql = "SELECT a.*, e.department, e.position, u.name AS employee_name, u.avatar AS employee_avatar
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            {$where}
            ORDER BY a.id DESC LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    foreach ($records as &$rec) {
        $rec['total_hours'] = $rec['total_hours'] !== null ? (float)$rec['total_hours'] : null;
    }

    sendResponse(true, 'Lấy lịch sử chấm công thành công.', $records, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
}
