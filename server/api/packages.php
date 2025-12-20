<?php
/**
 * Packages API Endpoint
 * Handles CRUD operations for tour packages
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
    error_log("Packages API Error: " . $e->getMessage());
    sendResponse(500, null, 'Internal server error');
}

/**
 * GET: Fetch packages
 * Query params: category, status, limit
 */
function handleGet($db) {
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : 'active';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    
    $query = "SELECT * FROM packages WHERE status = :status";
    
    if ($category) {
        $query .= " AND category = :category";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT :limit";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    
    $stmt->execute();
    $packages = $stmt->fetchAll();
    
    // Format image URLs
    foreach ($packages as &$package) {
        if ($package['image']) {
            $package['image'] = getUploadUrl($package['image']);
        }
    }
    
    sendResponse(200, $packages);
}

/**
 * POST: Create new package (with image upload)
 */
function handlePost($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Handle image upload if present
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagePath = uploadImage($_FILES['image']);
    }
    
    // If no file upload, check for base64 image in JSON
    if (!$imagePath && isset($data['image'])) {
        $imagePath = saveBase64Image($data['image']);
    }
    
    // Validate required fields
    if (empty($data['title_en']) || empty($data['description_en']) || empty($data['price'])) {
        sendResponse(400, null, 'Missing required fields');
    }
    
    $query = "INSERT INTO packages 
              (category, title_en, title_si, title_ta, description_en, description_si, description_ta, price, duration, image, status) 
              VALUES (:category, :title_en, :title_si, :title_ta, :description_en, :description_si, :description_ta, :price, :duration, :image, :status)";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':category', $data['category']);
    $stmt->bindParam(':title_en', $data['title_en']);
    $stmt->bindParam(':title_si', $data['title_si']);
    $stmt->bindParam(':title_ta', $data['title_ta']);
    $stmt->bindParam(':description_en', $data['description_en']);
    $stmt->bindParam(':description_si', $data['description_si']);
    $stmt->bindParam(':description_ta', $data['description_ta']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':duration', $data['duration']);
    $stmt->bindParam(':image', $imagePath);
    $status = $data['status'] ?? 'active';
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
    
    $query = "UPDATE packages SET 
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
    
    $query = "DELETE FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    
    if ($stmt->execute()) {
        sendResponse(200, null, 'Package deleted successfully');
    } else {
        sendResponse(500, null, 'Failed to delete package');
    }
}

/**
 * Upload image file
 * @param array $file $_FILES array
 * @return string Filename
 */
function uploadImage($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = getUploadPath($filename);
    
    // Create uploads directory if it doesn't exist
    $uploadDir = dirname($uploadPath);
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }
    
    throw new Exception('Failed to upload image');
}

/**
 * Save base64 encoded image
 * @param string $base64String
 * @return string Filename
 */
function saveBase64Image($base64String) {
    // Remove data URI prefix if present
    if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
        $extension = $matches[1];
        $base64String = substr($base64String, strpos($base64String, ',') + 1);
    } else {
        $extension = 'jpg';
    }
    
    $imageData = base64_decode($base64String);
    
    if ($imageData === false) {
        throw new Exception('Invalid base64 image');
    }
    
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = getUploadPath($filename);
    
    // Create uploads directory if it doesn't exist
    $uploadDir = dirname($uploadPath);
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (file_put_contents($uploadPath, $imageData)) {
        return $filename;
    }
    
    throw new Exception('Failed to save image');
}