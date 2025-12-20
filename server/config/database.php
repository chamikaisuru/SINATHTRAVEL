<?php
/**
 * Database Configuration
 * Using PDO for secure database connections
 */

class Database {
    // Database credentials - UPDATE THESE IF NEEDED
    private $host = "localhost";
    private $db_name = "sinath_travels";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            
            // Set error mode to exceptions
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Set default fetch mode to associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }
}

/**
 * Enhanced CORS Handler
 * Works for both development and production
 */
function enableCORS() {
    // Get the request origin
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    // Allowed origins for development
    $allowedOrigins = [
        'http://localhost:5000',
        'http://localhost:5173',
        'http://127.0.0.1:5000',
        'http://127.0.0.1:5173',
        'http://192.168.8.101:5000',
        'http://192.168.8.101:5173',
        'https://www.sinathtravels.com',
        'https://sinathtravels.com',
    ];
    
    // Check if origin is in allowed list
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        // For development, allow all local origins
        if (strpos($origin, 'localhost') !== false || 
            strpos($origin, '127.0.0.1') !== false ||
            strpos($origin, '192.168.') !== false ||
            strpos($origin, '10.0.') !== false) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            // Fallback for production
            header("Access-Control-Allow-Origin: *");
        }
    }
    
    // Required CORS headers
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 3600");
    header("Content-Type: application/json; charset=UTF-8");

    // Handle OPTIONS preflight request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit(0);
    }
}

/**
 * Send JSON response
 * @param int $status HTTP status code
 * @param mixed $data Response data
 * @param string $message Optional message
 */
function sendResponse($status, $data = null, $message = null) {
    http_response_code($status);
    
    // Ensure headers are set
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
    
    // Add success flag for easier client-side handling
    $response['success'] = ($status >= 200 && $status < 300);
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Get uploaded file path
 * @param string $filename
 * @return string Full file path
 */
function getUploadPath($filename) {
    $uploadDir = __DIR__ . '/../uploads/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    return $uploadDir . $filename;
}

/**
 * Get public URL for uploaded file
 * @param string $filename
 * @return string Public URL
 */
function getUploadUrl($filename) {
    if (empty($filename)) {
        return '';
    }
    
    // Get protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    
    // Get host
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Get base path
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = str_replace('/api', '', $scriptPath);
    $basePath = str_replace('/admin', '', $basePath);
    
    // Build full URL
    $url = "$protocol://$host$basePath/uploads/$filename";
    
    return $url;
}

/**
 * Validate and sanitize input
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Log error to file
 * @param string $message Error message
 * @param string $type Error type
 */
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