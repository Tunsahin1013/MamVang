<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - PURE PHP JWT IMPLEMENTATION
 * Alg: HS256 (HMAC-SHA256)
 */

class JWT {
    private static string $secret = 'canteen_jwt_secret_key_2026_super_secure_key_991823';

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Generate JWT Token
     */
    public static function encode(array $payload, int $expirySeconds = 86400): string {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expirySeconds;

        $base64Header = self::base64UrlEncode(json_encode($header));
        $base64Payload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "{$base64Header}.{$base64Payload}", self::$secret, true);
        $base64Signature = self::base64UrlEncode($signature);

        return "{$base64Header}.{$base64Payload}.{$base64Signature}";
    }

    /**
     * Decode and Validate JWT Token
     * Returns payload array or null if invalid/expired
     */
    public static function decode(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', "{$base64Header}.{$base64Payload}", self::$secret, true);
        $providedSignature = self::base64UrlDecode($base64Signature);

        if (!hash_equals($expectedSignature, $providedSignature)) {
            return null; // Invalid signature
        }

        $payload = json_decode(self::base64UrlDecode($base64Payload), true);
        if (!is_array($payload)) {
            return null;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null; // Expired
        }

        return $payload;
    }
}
