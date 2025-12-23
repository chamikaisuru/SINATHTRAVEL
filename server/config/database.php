<?php
/**
 * Database Configuration - FIXED WITH STOCK IMAGES SUPPORT
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
    $uploadDir = __DIR__ . '/../uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    return $uploadDir . $filename;
}

/**
 * FIXED: Get image URL - handles both stock images and uploads
 */
function getImageUrl($filename) {
    if (empty($filename)) {
        return '';
    }
    
    // If already a full URL, return as is
    if (strpos($filename, 'http://') === 0 || strpos($filename, 'https://') === 0) {
        return $filename;
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // CRITICAL FIX: Check if this is a stock image (no path prefix)
    // Stock images are just filenames like "dubai_skyline_with_b_8fae68a6.jpg"
    if (strpos($filename, '/') === false && strpos($filename, '\\') === false) {
        // It's a stock image - use Vite's asset system
        // Frontend will handle this through the @assets alias
        return "/src/assets/stock_images/" . $filename;
    }
    
    // Check if it's already a server-relative path
    if (strpos($filename, '/server/uploads/') === 0) {
        return "$protocol://$host$filename";
    }
    
    // Check if it's a path starting with /uploads/
    if (strpos($filename, '/uploads/') === 0) {
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = str_replace('/api', '', $scriptPath);
        $basePath = str_replace('/admin', '', $basePath);
        return "$protocol://$host$basePath$filename";
    }
    
    // Assume it's just a filename in uploads directory
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = str_replace('/api', '', $scriptPath);
    $basePath = str_replace('/admin', '', $basePath);
    
    return "$protocol://$host$basePath/uploads/$filename";
}

/**
 * Get stock image path for frontend
 */
function getStockImagePath($filename) {
    // Return path that Vite can resolve
    return "/src/assets/stock_images/" . $filename;
}

/**
 * Check if image is a stock image (no upload)
 */
function isStockImage($filename) {
    if (empty($filename)) {
        return false;
    }
    
    // Stock images have no path separators
    return (strpos($filename, '/') === false && strpos($filename, '\\') === false);
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