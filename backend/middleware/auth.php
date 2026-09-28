<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - AUTHENTICATION & ROLE MIDDLEWARE
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';

class AuthMiddleware {
    /**
     * Get bearer token from HTTP headers
     */
    private static function getBearerToken(): ?string {
        $headers = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            } elseif (isset($requestHeaders['authorization'])) {
                $headers = trim($requestHeaders['authorization']);
            }
        }

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/i', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Authenticate and authorize request by required roles
     * @param array $allowedRoles Array of allowed roles e.g. ['ADMIN', 'EMPLOYEE']
     * @return array Authenticated user record
     */
    public static function authorize(array $allowedRoles = []): array {
        $token = self::getBearerToken();

        if (!$token) {
            sendResponse(false, 'Truy cập bị từ chối: Chưa cung cấp JWT Token trong Authorization header.', null, 401);
        }

        $payload = JWT::decode($token);
        if (!$payload || !isset($payload['user_id'])) {
            sendResponse(false, 'Phiên đăng nhập không hợp lệ hoặc đã hết hạn. Vui lòng đăng nhập lại.', null, 401);
        }

        // Verify user against active database state
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, role, avatar, status FROM users WHERE id = ?");
        $stmt->execute([$payload['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            sendResponse(false, 'Tài khoản không tồn tại trên hệ thống.', null, 401);
        }

        if ($user['status'] !== 'ACTIVE') {
            sendResponse(false, 'Tài khoản của bạn đã bị khóa hoặc tạm ngưng hoạt động.', null, 403);
        }

        // Role check
        if (!empty($allowedRoles) && !in_array($user['role'], $allowedRoles)) {
            sendResponse(false, 'Bạn không có quyền thực hiện thao tác này (Yêu cầu quyền: ' . implode(', ', $allowedRoles) . ').', null, 403);
        }

        return $user;
    }
}
