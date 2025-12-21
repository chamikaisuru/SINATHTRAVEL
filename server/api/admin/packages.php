<?php
/**
 * COMPLETELY FIXED Admin Packages API
 * Now properly returns packages array, not auth data
 */

require_once '../../config/database.php';
require_once './auth.php';

enableCORS();

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

error_log("📦 PACKAGES API CALLED");
error_log("Method: " . $method);
error_log("Session ID: " . ($_SESSION['admin_session_id'] ?? 'none'));

// Verify admin authentication - this should NOT send a response, just return admin
try {
    $admin = verifyAdminAuth($db);
    error_log("✅ Admin verified in packages.php: " . $admin['username']);
} catch(Exception $e) {
    error_log("❌ Auth failed in packages.php: " . $e->getMessage());
    // verifyAdminAuth already sent the 401 response
    exit;
}

// Now handle the actual packages request
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
    error_log("❌ Packages Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendResponse(500, null, 'Internal server error: ' . $e->getMessage());
}

/**
 * GET: Fetch all packages
 * FIXED: Returns array of packages directly
 */
function handleGet($db) {
    error_log("📦 handleGet called");
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        // Get single package
        error_log("📦 Fetching single package: " . $id);
        $query = "SELECT * FROM packages WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $package = $stmt->fetch();
        
        if ($package && $package['image']) {
            $package['image'] = getUploadUrl($package['image']);
        }
        
        error_log("📦 Returning single package");
        sendResponse(200, $package);
    } else {
        // Get all packages with optional filters
        error_log("📦 Fetching all packages");
        
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
        $packages = $stmt->fetchAll();
        
        error_log("📦 Found " . count($packages) . " packages");
        
        // Format image URLs
        foreach ($packages as &$package) {
            if ($package['image']) {
                $package['image'] = getUploadUrl($package['image']);
            }
        }
        
        error_log("📦 Sending response with " . count($packages) . " packages");
        
        // CRITICAL FIX: Send packages array directly as data
        // The frontend expects an array, not { packages: [...] }
        sendResponse(200, $packages);
    }
}

/**
 * POST: Create new package with image upload
 */
function handlePost($db) {
    $data = $_POST;
    $imagePath = null;
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagePath = uploadImage($_FILES['image']);
    }
    
    // Validate required fields
    if (empty($data['title_en']) || empty($data['description_en']) || empty($data['price'])) {
        sendResponse(400, null, 'Missing required fields: title_en, description_en, price');
    }
    
    $query = "INSERT INTO packages 
              (category, title_en, title_si, title_ta, description_en, description_si, description_ta, 
               price, duration, image, status) 
              VALUES (:category, :title_en, :title_si, :title_ta, :description_en, :description_si, 
                      :description_ta, :price, :duration, :image, :status)";
    
    $stmt = $db->prepare($query);
    
    $category = $data['category'] ?? 'tour';
    $status = $data['status'] ?? 'active';
    
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':title_en', $data['title_en']);
    $stmt->bindParam(':title_si', $data['title_si']);
    $stmt->bindParam(':title_ta', $data['title_ta']);
    $stmt->bindParam(':description_en', $data['description_en']);
    $stmt->bindParam(':description_si', $data['description_si']);
    $stmt->bindParam(':description_ta', $data['description_ta']);
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

/**
 * PUT: Update existing package
 */
function handlePut($db) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        sendResponse(400, null, 'Package ID required');
    }
    
    $query = "SELECT * FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        sendResponse(404, null, 'Package not found');
    }
    
    $query = "UPDATE packages SET 
              category = :category,
              title_en = :title_en,
              title_si = :title_si,
              title_ta = :title_ta,
              description_en = :description_en,
              description_si = :description_si,
              description_ta = :description_ta,
              price = :price,
              duration = :duration,
              status = :status
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':category', $data['category']);
    $stmt->bindParam(':title_en', $data['title_en']);
    $stmt->bindParam(':title_si', $data['title_si']);
    $stmt->bindParam(':title_ta', $data['title_ta']);
    $stmt->bindParam(':description_en', $data['description_en']);
    $stmt->bindParam(':description_si', $data['description_si']);
    $stmt->bindParam(':description_ta', $data['description_ta']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':duration', $data['duration']);
    $stmt->bindParam(':status', $data['status']);
    
    if ($stmt->execute()) {
        sendResponse(200, null, 'Package updated successfully');
    } else {
        sendResponse(500, null, 'Failed to update package');
    }
}

/**
 * DELETE: Remove package
 */
function handleDelete($db) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        sendResponse(400, null, 'Package ID required');
    }
    
    // Get image path before deleting
    $query = "SELECT image FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->execute();
    $package = $stmt->fetch();
    
    if (!$package) {
        sendResponse(404, null, 'Package not found');
    }
    
    // Delete package
    $query = "DELETE FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    
    if ($stmt->execute()) {
        // Delete image file if exists
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

/**
 * Upload and validate image
 */
function uploadImage($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and WebP allowed.');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large. Maximum size is 5MB.');
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