<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - DATABASE CONNECTION
 * PDO MySQL Driver
 * Database: canteen_management
 */

class Database {
    private static ?PDO $instance = null;

    private string $host;
    private string $port;
    private string $db_name;
    private string $username;
    private string $password;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->port = getenv('DB_PORT') ?: '3306';
        $this->db_name = getenv('DB_NAME') ?: 'canteen_management';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    }

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $db = new self();
            $dsn = "mysql:host={$db->host};port={$db->port};dbname={$db->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $db->username, $db->password, $options);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi kết nối cơ sở dữ liệu MySQL: ' . $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        return self::$instance;
    }
}
