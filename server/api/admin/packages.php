<?php
/**
 * FIXED Admin Packages API - Proper Image URL Generation
 * Replace: server/api/admin/packages.php
 */

// STEP 1: Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// STEP 2: Load dependencies
require_once '../../config/database.php';

// STEP 3: Enable CORS
enableCORS();

error_log("========== PACKAGES API ==========");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Session ID: " . session_id());
error_log("Admin Session ID: " . ($_SESSION['admin_session_id'] ?? 'NONE'));

// STEP 4: Initialize database
$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// STEP 5: Verify authentication
$sessionId = $_SESSION['admin_session_id'] ?? null;

if (!$sessionId) {
    error_log("❌ No session ID in packages.php");
    sendResponse(401, null, 'Not authenticated');
    exit;
}

try {
    $query = "SELECT au.* FROM admin_users au 
              JOIN admin_sessions s ON au.id = s.admin_id 
              WHERE s.id = :session_id AND s.expires_at > NOW() AND au.status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':session_id', $sessionId);
    $stmt->execute();
    
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        error_log("❌ Invalid session in packages.php");
        sendResponse(401, null, 'Session expired');
        exit;
    }
    
    error_log("✅ Admin verified: " . $admin['username']);
    
} catch(Exception $e) {
    error_log("❌ Auth error: " . $e->getMessage());
    sendResponse(401, null, 'Authentication error');
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
 * FIXED: Convert database image path to frontend-usable URL
 */
function formatImagePath($imagePath) {
    if (empty($imagePath)) {
        return null;
    }
    
    error_log("🖼️ Formatting image path: " . $imagePath);
    
    // If already a full URL, return as is
    if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
        error_log("🖼️ Already full URL");
        return $imagePath;
    }
    
    // Normalize path separators
    $imagePath = str_replace('\\', '/', $imagePath);
    
    // Remove any leading slashes or path prefixes
    $imagePath = ltrim($imagePath, '/');
    $imagePath = str_replace('server/uploads/', '', $imagePath);
    $imagePath = str_replace('uploads/', '', $imagePath);
    
    // Check if it's a stock image (no timestamp pattern in filename)
    // Uploaded images have pattern: {random}_{timestamp}.{ext}
    // Stock images: descriptive_name_{hash}.jpg
    $isStockImage = !preg_match('/^\w+_\d{10,}\.(jpg|jpeg|png|webp)$/i', $imagePath);
    
    if ($isStockImage) {
        // Stock image - return path for Vite to resolve
        error_log("🖼️ Stock image detected: " . $imagePath);
        return '/src/assets/stock_images/' . $imagePath;
    } else {
        // Uploaded image - return full URL to server/uploads
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        $url = "$protocol://$host/server/uploads/$imagePath";
        
        error_log("🖼️ Uploaded image URL: " . $url);
        return $url;
    }
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
        
        if (!$stmt->execute()) {
            error_log("❌ Query execution failed");
            sendResponse(500, null, 'Database query failed');
            return;
        }
        
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($package) {
            // Format image path
            $package['image'] = formatImagePath($package['image']);
        }
        
        sendResponse(200, $package);
        return;
    }
    
    // GET ALL PACKAGES
    error_log("📦 Fetching ALL packages");
    
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
    
    error_log("📦 SQL Query: " . $query);
    
    // Prepare statement
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        error_log("❌ Failed to prepare statement");
        sendResponse(500, null, 'Failed to prepare database query');
        return;
    }
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // Execute query
    $executeResult = $stmt->execute();
    
    if (!$executeResult) {
        error_log("❌ Query execution failed");
        sendResponse(500, null, 'Database query execution failed');
        return;
    }
    
    error_log("✅ Query executed successfully");
    
    // Fetch all results
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($packages === false) {
        error_log("❌ fetchAll() returned FALSE");
        sendResponse(500, null, 'Failed to fetch packages from database');
        return;
    }
    
    $packageCount = count($packages);
    error_log("📦 Fetched " . $packageCount . " packages from database");
    
    // CRITICAL FIX: Format image paths for ALL packages
    foreach ($packages as &$package) {
        $package['image'] = formatImagePath($package['image']);
    }
    unset($package);
    
    error_log("📦 Sending response with " . $packageCount . " packages");
    
    // Send response
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
            // Upload image and get JUST the filename
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
    $stmt->bindParam(':image', $imagePath); // Store ONLY filename in database
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
 * PUT: Update package - FIXED TO HANDLE MULTIPART FORM DATA
 */
function handlePut($db) {
    error_log("📝 handlePut called");
    error_log("📝 Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none'));
    error_log("📝 Has FILES: " . (isset($_FILES['image']) ? 'yes' : 'no'));
    
    // CRITICAL FIX: PUT with file upload comes as POST with _method=PUT
    // OR as multipart PUT data
    $data = [];
    $isMultipart = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;
    
    if ($isMultipart || $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Multipart form data (with file upload)
        $data = $_POST;
        error_log("📝 Using POST data: " . json_encode($data));
    } else {
        // Regular PUT (no file)
        parse_str(file_get_contents("php://input"), $data);
        error_log("📝 Using parsed PUT data: " . json_encode($data));
    }
    
    if (empty($data['id'])) {
        error_log("❌ No package ID");
        sendResponse(400, null, 'Package ID required');
        return;
    }
    
    // Check if package exists and get current image
    $query = "SELECT * FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->execute();
    
    $existingPackage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingPackage) {
        error_log("❌ Package not found: " . $data['id']);
        sendResponse(404, null, 'Package not found');
        return;
    }
    
    error_log("✅ Package found: " . $existingPackage['title_en']);
    error_log("📝 Current image: " . ($existingPackage['image'] ?? 'none'));
    
    // Handle new image upload
    $newImagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            // Upload new image
            $newImagePath = uploadImage($_FILES['image']);
            error_log("✅ New image uploaded: " . $newImagePath);
            
            // Delete old image if it exists and is not a stock image
            if (!empty($existingPackage['image'])) {
                $isStockImage = !preg_match('/^\w+_\d{10,}\.(jpg|jpeg|png|webp)$/i', $existingPackage['image']);
                
                if (!$isStockImage) {
                    $oldImagePath = __DIR__ . '/../../uploads/' . $existingPackage['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                        error_log("🗑️ Deleted old image: " . $oldImagePath);
                    }
                }
            }
        } catch(Exception $e) {
            error_log("❌ Image upload failed: " . $e->getMessage());
            sendResponse(400, null, 'Image upload failed: ' . $e->getMessage());
            return;
        }
    }
    
    // Build UPDATE query
    if ($newImagePath) {
        // Update including image
        $query = "UPDATE packages SET 
                  category = :category,
                  title_en = :title_en,
                  description_en = :description_en,
                  price = :price,
                  duration = :duration,
                  status = :status,
                  image = :image
                  WHERE id = :id";
    } else {
        // Update without image
        $query = "UPDATE packages SET 
                  category = :category,
                  title_en = :title_en,
                  description_en = :description_en,
                  price = :price,
                  duration = :duration,
                  status = :status
                  WHERE id = :id";
    }
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':category', $data['category']);
    $stmt->bindParam(':title_en', $data['title_en']);
    $stmt->bindParam(':description_en', $data['description_en']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':duration', $data['duration']);
    $stmt->bindParam(':status', $data['status']);
    
    if ($newImagePath) {
        $stmt->bindParam(':image', $newImagePath);
    }
    
    if ($stmt->execute()) {
        error_log("✅ Package updated successfully");
        
        // Get updated package to return with formatted image URL
        $query = "SELECT * FROM packages WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $data['id']);
        $stmt->execute();
        $updatedPackage = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($updatedPackage) {
            $updatedPackage['image'] = formatImagePath($updatedPackage['image']);
        }
        
        sendResponse(200, $updatedPackage, 'Package updated successfully');
    } else {
        error_log("❌ Failed to update package");
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
        // Delete image file if it's an uploaded image (not stock)
        if (!empty($package['image'])) {
            $isStockImage = !preg_match('/^\w+_\d{10,}\.(jpg|jpeg|png|webp)$/i', $package['image']);
            
            if (!$isStockImage) {
                $imagePath = __DIR__ . '/../../uploads/' . $package['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                    error_log("🗑️ Deleted image: " . $imagePath);
                }
            }
        }
        sendResponse(200, null, 'Package deleted successfully');
    } else {
        sendResponse(500, null, 'Failed to delete package');
    }
}

/**
 * Upload image - Returns ONLY filename
 */
function uploadImage($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Allowed: JPG, PNG, WebP');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large. Maximum 5MB');
    }
    
    // Generate unique filename with timestamp
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Upload to server/uploads directory
    $uploadDir = __DIR__ . '/../../uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        error_log("✅ File uploaded: " . $uploadPath);
        // Return ONLY the filename, not the full path
        return $filename;
    }
    
    throw new Exception('Failed to upload image');
}
?>