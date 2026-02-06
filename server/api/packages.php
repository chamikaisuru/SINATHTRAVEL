<?php
/**
 * PUBLIC Packages API
 * Fetches packages for the website frontend
 */
require_once '../config/database.php';
enableCORS();

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if ($id) {
            // Get single package
            $query = "SELECT * FROM packages WHERE id = :id AND status = 'active'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $package = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($package) {
                if ($package['image']) {
                    $package['image'] = getImageUrl($package['image']);
                }
                sendResponse(200, $package);
            } else {
                sendResponse(404, null, 'Package not found');
            }
        } else {
            // Get list of packages
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
            
            $query = "SELECT * FROM packages WHERE status = 'active'";
            
            if ($category && $category !== 'all') {
                $query .= " AND category = :category";
            }
            
            $query .= " ORDER BY created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $db->prepare($query);
            
            if ($category && $category !== 'all') {
                $stmt->bindParam(':category', $category);
            }
            
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format images
            foreach ($packages as &$package) {
                if ($package['image']) {
                    $package['image'] = getImageUrl($package['image']);
                }
            }
            
            // Public API returns data directly in a wrapped response
            sendResponse(200, $packages);
        }
    } catch (PDOException $e) {
        sendResponse(500, null, 'Database error: ' . $e->getMessage());
    }
} else {
    sendResponse(405, null, 'Method not allowed. Public API is read-only.');
}
?>