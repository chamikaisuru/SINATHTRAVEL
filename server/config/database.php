<?php
/**
 * Database Configuration
 * Using PDO for secure database connections
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

/**
 * ULTIMATE CORS FIX - credentials: 'include' සඳහා
 */
function enableCORS() {
    // Check if headers already sent
    if (headers_sent($file, $line)) {
        error_log("Headers already sent in $file on line $line");
        return;
    }
    
    // Get origin
    $origin = '';
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $origin = $_SERVER['HTTP_ORIGIN'];
    }
    
    // Log for debugging
    error_log("Request Origin: " . $origin);
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    
    // Allowed origins - EXACT matches only
    $allowedOrigins = [
        'http://localhost:5000',
        'http://localhost:8080',
        'http://127.0.0.1:5000',
        'http://127.0.0.1:8080',
    ];
    
    // Set appropriate origin
    $allowedOrigin = 'http://localhost:5000'; // default
    
    if (in_array($origin, $allowedOrigins)) {
        $allowedOrigin = $origin;
        error_log("Matched exact origin: " . $origin);
    } elseif (preg_match('#^http://(?:localhost|127\.0\.0\.1|192\.168\.\d+\.\d+)(?::\d+)?$#', $origin)) {
        $allowedOrigin = $origin;
        error_log("Matched pattern origin: " . $origin);
    }
    
    // CRITICAL: Set headers in correct order
    header("Access-Control-Allow-Origin: " . $allowedOrigin);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Max-Age: 3600");
    header("Content-Type: application/json; charset=UTF-8");
    
    // Log headers
    error_log("Set CORS Origin to: " . $allowedOrigin);
    
    // Handle OPTIONS preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        error_log("Handling OPTIONS preflight request");
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

function getUploadPath($filename) {
    $uploadDir = __DIR__ . '/../uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    return $uploadDir . $filename;
}

function getUploadUrl($filename) {
    if (empty($filename)) {
        return '';
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = str_replace('/api', '', $scriptPath);
    $basePath = str_replace('/admin', '', $basePath);
    
    $url = "$protocol://$host$basePath/uploads/$filename";
    
    return $url;
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