<?php
/**
 * CORS Test Endpoint
 * Use this to verify CORS is working correctly
 */

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Get request details
$requestMethod = $_SERVER['REQUEST_METHOD'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'No origin header';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'No user agent';
$referer = $_SERVER['HTTP_REFERER'] ?? 'No referer';
$host = $_SERVER['HTTP_HOST'] ?? 'No host';

// Return test response
echo json_encode([
    'success' => true,
    'message' => 'CORS is working correctly! ✅',
    'server_info' => [
        'time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get(),
        'php_version' => phpversion(),
    ],
    'request_info' => [
        'method' => $requestMethod,
        'origin' => $origin,
        'host' => $host,
        'referer' => $referer,
        'user_agent' => substr($userAgent, 0, 100),
    ],
    'cors_headers' => [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept',
    ]
], JSON_PRETTY_PRINT);