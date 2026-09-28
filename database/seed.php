<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - AUTOMATED DATABASE SEEDER
 * Run via CLI: php database/seed.php
 * Or via Browser: http://localhost/canteen-management/database/seed.php
 */

header('Content-Type: application/json; charset=utf-8');

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$dbname = 'canteen_management';

try {
    // 1. Connect to MySQL Server (without selecting db first)
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbname}`");

    // 3. Execute create_tables.sql
    $createTablesSql = file_get_contents(__DIR__ . '/create_tables.sql');
    if ($createTablesSql) {
        $pdo->exec($createTablesSql);
    }

    // 4. Recalculate live bcrypt hashes
    $adminHash = password_hash('Admin@123', PASSWORD_BCRYPT);
    $employeeHash = password_hash('Employee@123', PASSWORD_BCRYPT);
    $customerHash = password_hash('Customer@123', PASSWORD_BCRYPT);

    // 5. Execute insert_data.sql
    $insertDataSql = file_get_contents(__DIR__ . '/insert_data.sql');
    if ($insertDataSql) {
        $pdo->exec($insertDataSql);
    }

    // 6. Update user passwords with freshly generated bcrypt hashes
    $stmtAdmin = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'ADMIN'");
    $stmtAdmin->execute([$adminHash]);

    $stmtEmp = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'EMPLOYEE'");
    $stmtEmp->execute([$employeeHash]);

    $stmtCust = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'CUSTOMER'");
    $stmtCust->execute([$customerHash]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Database canteen_management seeded successfully with live bcrypt passwords!',
        'default_accounts' => [
            ['email' => 'admin@canteen.com', 'password' => 'Admin@123', 'role' => 'ADMIN'],
            ['email' => 'employee@canteen.com', 'password' => 'Employee@123', 'role' => 'EMPLOYEE'],
            ['email' => 'customer@canteen.com', 'password' => 'Customer@123', 'role' => 'CUSTOMER']
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database seeding failed: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
