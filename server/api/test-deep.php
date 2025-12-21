<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Test database connection
try {
    $db = new PDO("mysql:host=localhost;dbname=sinath_travels", "root", "");
    $db_status = "Database Connected ✅";
} catch(PDOException $e) {
    $db_status = "Database Error: " . $e->getMessage();
}

echo json_encode([
    "status" => "success",
    "message" => "Deep API Test Working! ✅",
    "location" => __FILE__,
    "database" => $db_status,
    "request_method" => $_SERVER['REQUEST_METHOD'],
    "server_port" => $_SERVER['SERVER_PORT']
]);