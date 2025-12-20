<?php
/**
 * Services API Endpoint
 * Handles main services data
 */

require_once '../config/database.php';

enableCORS();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            handleGet($db);
            break;
        
        default:
            sendResponse(405, null, 'Method not allowed');
    }
} catch(Exception $e) {
    error_log("Services API Error: " . $e->getMessage());
    sendResponse(500, null, 'Internal server error');
}

/**
 * GET: Fetch active services
 */
function handleGet($db) {
    $query = "SELECT * FROM services WHERE status = 1 ORDER BY display_order ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll();
    
    sendResponse(200, $services);
}