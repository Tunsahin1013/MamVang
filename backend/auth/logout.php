<?php
/**
 * CANTEEN MANAGEMENT SYSTEM - LOGOUT API
 * Endpoint: POST /backend/auth/logout.php
 */

require_once __DIR__ . '/../config/cors.php';

// In JWT stateless architecture, client deletes token from localStorage/cookies.
// This endpoint provides audit logging and server confirmation.
sendResponse(true, 'Đăng xuất thành công. Phiên làm việc đã kết thúc.', null, 200);
