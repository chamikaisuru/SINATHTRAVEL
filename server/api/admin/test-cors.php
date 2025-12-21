<?php
/**
 * CORS TEST FILE
 * Save to: D:\xampp\htdocs\sinath-travels\server\api\admin\test-cors.php
 * Test URL: http://localhost:8080/sinath-travels/server/api/admin/test-cors.php
 */

// ABSOLUTELY NO OUTPUT before headers!
// No spaces, no BOM, nothing before <?php

// Get origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'no-origin';

// Log for debugging
error_log("🔍 CORS Test - Origin: " . $origin);
error_log("🔍 Request Method: " . $_SERVER['REQUEST_METHOD']);

// Allowed origins - EXACT match only
$allowedOrigins = [
    'http://localhost:5000',
    'http://127.0.0.1:5000',
    'http://localhost:8080',
];

// Check origin
$allowOrigin = '';
foreach ($allowedOrigins as $allowed) {
    if ($origin === $allowed) {
        $allowOrigin = $origin;
        break;
    }
}

// CRITICAL: Set headers in this EXACT order
if (!empty($allowOrigin)) {
    header("Access-Control-Allow-Origin: " . $allowOrigin);
    header("Access-Control-Allow-Credentials: true");
    error_log("✅ Allowed origin: " . $allowOrigin);
} else {
    error_log("❌ Origin not allowed: " . $origin);
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Access-Control-Max-Age: 3600");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("✅ OPTIONS request handled");
    http_response_code(200);
    exit(0);
}

// Success response
$response = [
    'success' => true,
    'message' => '✅ CORS is working!',
    'debug' => [
        'origin_received' => $origin,
        'origin_allowed' => $allowOrigin,
        'method' => $_SERVER['REQUEST_METHOD'],
        'timestamp' => date('Y-m-d H:i:s'),
        'server_port' => $_SERVER['SERVER_PORT'],
        'script_path' => __FILE__,
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
exit;