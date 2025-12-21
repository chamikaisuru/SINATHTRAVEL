<?php
/**
 * Direct CORS Test - No dependencies
 */

// Get origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Allowed origins
$allowedOrigins = [
    'http://localhost:5000',
    'http://127.0.0.1:5000',
];

// Set origin
$allowedOrigin = 'http://localhost:5000';
if (in_array($origin, $allowedOrigins)) {
    $allowedOrigin = $origin;
}

// Set CORS headers
header("Access-Control-Allow-Origin: " . $allowedOrigin);
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Test response
echo json_encode([
    'success' => true,
    'message' => 'CORS is working! ✅',
    'origin_received' => $origin,
    'origin_allowed' => $allowedOrigin,
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers_sent' => headers_sent(),
]);