<?php
/**
 * Database Configuration - FIXED IMAGE URL GENERATION
 * Replace: server/config/database.php
 */

class Database {
    private $host = "localhost";
    private $db_name = "sinath_travels";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }
}

$GLOBALS['_CORS_HEADERS_SENT'] = $GLOBALS['_CORS_HEADERS_SENT'] ?? false;

/**
 * CORS Configuration
 */
function enableCORS() {
    if ($GLOBALS['_CORS_HEADERS_SENT']) {
        return;
    }
    
    if (headers_sent($file, $line)) {
        error_log("⚠️ Headers already sent in $file on line $line");
        return;
    }
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    $allowedOrigins = [
        'http://localhost:5000',
        'http://127.0.0.1:5000',
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://192.168.8.101:5000',
        'http://192.168.8.101:8080',
    ];
    
    $allowOrigin = '';
    if (in_array($origin, $allowedOrigins)) {
        $allowOrigin = $origin;
    } elseif (preg_match('#^http://(?:localhost|127\.0\.0\.1|192\.168\.\d+\.\d+):\d+$#', $origin)) {
        $allowOrigin = $origin;
    }
    
    $existingHeaders = headers_list();
    $hasOriginHeader = false;
    foreach ($existingHeaders as $h) {
        if (stripos($h, 'Access-Control-Allow-Origin') === 0) {
            $hasOriginHeader = true;
            break;
        }
    }

    if ($allowOrigin && !$hasOriginHeader) {
        header("Access-Control-Allow-Origin: $allowOrigin");
        header("Access-Control-Allow-Credentials: true");
    }
    
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Max-Age: 3600");
    header("Content-Type: application/json; charset=UTF-8");
    
    $GLOBALS['_CORS_HEADERS_SENT'] = true;
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit(0);
    }
}

function sendResponse($status, $data = null, $message = null) {
    http_response_code($status);
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
    }
    
    $response = [];
    
    if ($message !== null) {
        $response['message'] = $message;
    }
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    $response['success'] = ($status >= 200 && $status < 300);
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Get upload path for saving files
 */
function getUploadPath($filename) {
    // FIXED: Normalize path separators
    $filename = str_replace('\\', '/', $filename);
    
    $uploadDir = __DIR__ . '/../uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    return $uploadDir . $filename;
}

/**
 * COMPLETELY FIXED: Get image URL for frontend display
 */
function getImageUrl($filename) {
    if (empty($filename)) {
        return '';
    }
    
    error_log("🖼️ getImageUrl called with: " . $filename);
    
    // If already a full URL, return as is
    if (strpos($filename, 'http://') === 0 || strpos($filename, 'https://') === 0) {
        error_log("🖼️ Already full URL: " . $filename);
        return $filename;
    }
    
    // Normalize path separators
    $filename = str_replace('\\', '/', $filename);
    
    // CRITICAL FIX: Check if this is a stock image (just filename, no path)
    // Stock images: "dubai_skyline_with_b_8fae68a6.jpg"
    // Uploaded images: "694b0de204527_1766526434.png"
    if (strpos($filename, '/') === false) {
        // Could be either stock or uploaded
        // Check if file exists in uploads directory
        $uploadPath = __DIR__ . '/../uploads/' . $filename;
        
        if (file_exists($uploadPath)) {
            // It's an uploaded file
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
            
            // FIXED: Construct proper URL for uploaded files
            // From: D:\xampp\htdocs\server\uploads\image.png
            // To: http://localhost:8080/server/uploads/image.png
            $url = "$protocol://$host/server/uploads/$filename";
            
            error_log("🖼️ Uploaded image URL: " . $url);
            return $url;
        } else {
            // It's a stock image - return path for Vite to resolve
            error_log("🖼️ Stock image path: /src/assets/stock_images/" . $filename);
            return "/src/assets/stock_images/" . $filename;
        }
    }
    
    // If it has a path, handle accordingly
    if (strpos($filename, '/server/uploads/') === 0) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        return "$protocol://$host$filename";
    }
    
    if (strpos($filename, '/uploads/') === 0) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        return "$protocol://$host/server$filename";
    }
    
    // Default: assume it's in uploads
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
    return "$protocol://$host/server/uploads/$filename";
}

/**
 * Check if image is a stock image
 */
function isStockImage($filename) {
    if (empty($filename)) {
        return false;
    }
    
    // Normalize
    $filename = str_replace('\\', '/', $filename);
    
    // Stock images have no path separators AND don't exist in uploads
    if (strpos($filename, '/') === false) {
        $uploadPath = __DIR__ . '/../uploads/' . $filename;
        return !file_exists($uploadPath);
    }
    
    return false;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function logError($message, $type = 'ERROR') {
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    
    error_log($logMessage, 3, $logFile);
}