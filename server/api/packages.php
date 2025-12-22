<?php
/**
 * Admin Packages API - FINAL FIXED VERSION
 * Save to: server/api/admin/packages.php
 */

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
require_once './auth.php';

enableCORS();

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

error_log("📦 ADMIN PACKAGES API - Method: " . $method);

// Verify admin authentication
try {
    $admin = verifyAdminAuth($db);
    error_log("✅ Admin verified: " . $admin['username']);
} catch(Exception $e) {
    error_log("❌ Auth failed: " . $e->getMessage());
    exit;
}

// Handle request
try {
    switch($method) {
        case 'GET':
            handleGet($db);
            break;
        
        case 'POST':
            handlePost($db);
            break;
        
        case 'PUT':
            handlePut($db);
            break;
        
        case 'DELETE':
            handleDelete($db);
            break;
        
        default:
            sendResponse(405, null, 'Method not allowed');
    }
} catch(Exception $e) {
    error_log("❌ Error: " . $e->getMessage());
    sendResponse(500, null, 'Internal server error: ' . $e->getMessage());
}

function handleGet($db) {
    error_log("📦 GET request");
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        // Single package
        $query = "SELECT * FROM packages WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($package && $package['image']) {
            $package['image'] = getUploadUrl($package['image']);
        }
        
        sendResponse(200, $package);
    } else {
        // All packages
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        
        $query = "SELECT * FROM packages WHERE 1=1";
        
        if ($status) {
            $query .= " AND status = :status";
        }
        if ($category) {
            $query .= " AND category = :category";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        error_log("📦 Query: " . $query);
        
        $stmt = $db->prepare($query);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        if ($category) {
            $stmt->bindParam(':category', $category);
        }
        
        $stmt->execute();
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("📦 Found " . count($packages) . " packages");
        
        // Format image URLs
        foreach ($packages as &$package) {
            if ($package['image']) {
                $package['image'] = getUploadUrl($package['image']);
            }
        }
        
        // CRITICAL FIX: Return array directly, NOT wrapped in object
        error_log("📦 Returning " . count($packages) . " packages as array");
        
        http_response_code(200);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'data' => $packages,
            'count' => count($packages)
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function handlePost($db) {
    $data = $_POST;
    $imagePath = null;
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagePath = uploadImage($_FILES['image']);
    }
    
    // Validate
    if (empty($data['title_en']) || empty($data['description_en']) || empty($data['price'])) {
        sendResponse(400, null, 'Missing required fields');
    }
    
    $query = "INSERT INTO packages 
              (category, title_en, description_en, price, duration, image, status) 
              VALUES (:category, :title_en, :description_en, :price, :duration, :image, :status)";
    
    $stmt = $db->prepare($query);
    
    $category = $data['category'] ?? 'tour';
    $status = $data['status'] ?? 'active';
    
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':title_en', $data['title_en']);
    $stmt->bindParam(':description_en', $data['description_en']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':duration', $data['duration']);
    $stmt->bindParam(':image', $imagePath);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        sendResponse(201, ['id' => $db->lastInsertId()], 'Package created successfully');
    } else {
        sendResponse(500, null, 'Failed to create package');
    }
}

function handlePut($db) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        sendResponse(400, null, 'Package ID required');
    }
    
    $query = "UPDATE packages SET 
              category = :category,
              title_en = :title_en,
              description_en = :description_en,
              price = :price,
              duration = :duration,
              status = :status
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':category', $data['category']);
    $stmt->bindParam(':title_en', $data['title_en']);
    $stmt->bindParam(':description_en', $data['description_en']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':duration', $data['duration']);
    $stmt->bindParam(':status', $data['status']);
    
    if ($stmt->execute()) {
        sendResponse(200, null, 'Package updated successfully');
    } else {
        sendResponse(500, null, 'Failed to update package');
    }
}

function handleDelete($db) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        sendResponse(400, null, 'Package ID required');
    }
    
    // Get image before deleting
    $query = "SELECT image FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->execute();
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) {
        sendResponse(404, null, 'Package not found');
    }
    
    // Delete package
    $query = "DELETE FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    
    if ($stmt->execute()) {
        // Delete image file
        if ($package['image']) {
            $imagePath = getUploadPath($package['image']);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        sendResponse(200, null, 'Package deleted successfully');
    } else {
        sendResponse(500, null, 'Failed to delete package');
    }
}

function uploadImage($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = getUploadPath($filename);
    
    $uploadDir = dirname($uploadPath);
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }
    
    throw new Exception('Failed to upload image');
}
?>