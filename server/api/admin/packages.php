<?php
/**
 * COMPLETELY FIXED Admin Packages API
 * CRITICAL FIX: Session must be started BEFORE any configuration
 * Replace: server/api/admin/packages.php
 */

// STEP 1: Start session FIRST (before anything else)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// STEP 2: Load dependencies AFTER session started
require_once '../../config/database.php';
require_once './auth.php';

// STEP 3: Enable CORS
enableCORS();

error_log("========== PACKAGES API ==========");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Session ID: " . session_id());
error_log("Admin Session ID: " . ($_SESSION['admin_session_id'] ?? 'none'));

// STEP 4: Initialize database
$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// STEP 5: Verify authentication
try {
    $admin = verifyAdminAuth($db);
    error_log("✅ Admin verified: " . $admin['username']);
} catch(Exception $e) {
    error_log("❌ Auth failed: " . $e->getMessage());
    exit;
}

// STEP 6: Handle the request
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
    error_log("Stack trace: " . $e->getTraceAsString());
    sendResponse(500, null, 'Internal server error: ' . $e->getMessage());
}

/**
 * GET: Fetch packages
 */
function handleGet($db) {
    error_log("📦 handleGet called");
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        // Single package
        error_log("📦 Fetching single package: " . $id);
        $query = "SELECT * FROM packages WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($package && $package['image']) {
            $package['image'] = getUploadUrl($package['image']);
        }
        
        error_log("📦 Returning single package");
        sendResponse(200, $package);
        return;
    }
    
    // Get all packages
    error_log("📦 Fetching all packages");
    
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    
    // Build query
    $query = "SELECT * FROM packages WHERE 1=1";
    $params = [];
    
    if ($status) {
        $query .= " AND status = :status";
        $params[':status'] = $status;
    }
    if ($category) {
        $query .= " AND category = :category";
        $params[':category'] = $category;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    error_log("📦 Query: " . $query);
    error_log("📦 Params: " . json_encode($params));
    
    $stmt = $db->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    
    // CRITICAL: Use fetchAll with PDO::FETCH_ASSOC
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("📦 Query executed");
    error_log("📦 Rows returned: " . $stmt->rowCount());
    error_log("📦 Packages count: " . count($packages));
    
    // Format image URLs
    foreach ($packages as &$package) {
        if (!empty($package['image'])) {
            $package['image'] = getUploadUrl($package['image']);
        }
    }
    unset($package); // Break reference
    
    error_log("📦 Sending response with " . count($packages) . " packages");
    sendResponse(200, $packages, 'Packages retrieved successfully');
}

/**
 * POST: Create new package
 */
function handlePost($db) {
    error_log("📦 handlePost called");
    
    $data = $_POST;
    $imagePath = null;
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $imagePath = uploadImage($_FILES['image']);
            error_log("📦 Image uploaded: " . $imagePath);
        } catch(Exception $e) {
            error_log("📦 Image upload failed: " . $e->getMessage());
            sendResponse(400, null, 'Image upload failed: ' . $e->getMessage());
            return;
        }
    }
    
    // Validate
    if (empty($data['title_en']) || empty($data['description_en']) || empty($data['price'])) {
        error_log("📦 Validation failed");
        sendResponse(400, null, 'Missing required fields');
        return;
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
        $newId = $db->lastInsertId();
        error_log("📦 Package created with ID: " . $newId);
        sendResponse(201, ['id' => $newId], 'Package created successfully');
    } else {
        error_log("📦 Failed to create package");
        sendResponse(500, null, 'Failed to create package');
    }
}

/**
 * PUT: Update package
 */
function handlePut($db) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        sendResponse(400, null, 'Package ID required');
        return;
    }
    
    // Check if package exists
    $query = "SELECT * FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        sendResponse(404, null, 'Package not found');
        return;
    }
    
    // Update package
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

/**
 * DELETE: Remove package
 */
function handleDelete($db) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        sendResponse(400, null, 'Package ID required');
        return;
    }
    
    // Get image before deleting
    $query = "SELECT image FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->execute();
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) {
        sendResponse(404, null, 'Package not found');
        return;
    }
    
    // Delete package
    $query = "DELETE FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    
    if ($stmt->execute()) {
        // Delete image file
        if (!empty($package['image'])) {
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
 * Upload image
 */
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