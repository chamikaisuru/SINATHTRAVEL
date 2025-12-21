<?php
/**
 * PUBLIC Packages API (No Authentication Required)
 * For the public website ONLY
 * Path: server/api/packages.php
 * 
 * Admin packages use: server/api/admin/packages.php
 */

require_once '../config/database.php';

enableCORS();

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

error_log("📦 PUBLIC PACKAGES API CALLED");

try {
    if ($method !== 'GET') {
        sendResponse(405, null, 'Method not allowed');
    }
    
    // Get active packages only
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    
    $query = "SELECT * FROM packages WHERE status = 'active'";
    
    if ($category) {
        $query .= " AND category = :category";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT :limit";
    
    $stmt = $db->prepare($query);
    
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $packages = $stmt->fetchAll();
    
    // Format image URLs
    foreach ($packages as &$package) {
        if ($package['image']) {
            $package['image'] = getUploadUrl($package['image']);
        }
    }
    
    error_log("📦 PUBLIC: Returning " . count($packages) . " active packages");
    
    sendResponse(200, $packages);
    
} catch(Exception $e) {
    error_log("❌ PUBLIC Packages Error: " . $e->getMessage());
    sendResponse(500, null, 'Internal server error');
}
?>