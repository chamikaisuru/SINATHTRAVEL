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
 * FIXED CORS Handler - ඔයාගේ issue එක solve කරන්න
 */
function enableCORS() {
    // CRITICAL: Output කරපු දෙයක් නැත්නම් විතරක් headers set කරන්න
    if (headers_sent()) {
        return;
    }
    
    // Get the request origin
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    // Development සඳහා allow කරන origins
    $allowedOrigins = [
         'http://localhost:5000',
    'http://localhost:8080',  // මේක add කරන්න
    'http://127.0.0.1:8080',  // මේකත් add කරන්න
    'http://192.168.8.101:8080',  // මේකත් add කරන්න
        'http://localhost:5000',
        'http://localhost:5173',
        'http://127.0.0.1:5000',
        'http://127.0.0.1:5173',
        'http://192.168.8.101:5000',
        'http://192.168.8.101:5173',
        'http://192.168.8.101:8080',
        'https://www.sinathtravels.com',
        'https://sinathtravels.com',
    ];
    
    // Check origin and set appropriate header
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    } elseif (preg_match('/^http:\/\/(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+|10\.0\.\d+\.\d+)(:\d+)?$/', $origin)) {
        // Local development origins
        header("Access-Control-Allow-Origin: $origin");
    } else {
        // Production fallback
        header("Access-Control-Allow-Origin: *");
    }
    
    // CORS headers - මෙහෙම අනිවාර්යෙන්ම තියෙන්න ඕන
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400"); // 24 hours
    header("Content-Type: application/json; charset=UTF-8");

    // OPTIONS preflight request එක handle කරන්න
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit(0);
    }
}

/**
 * Send JSON response
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
    
    $response['success'] = ($status >= 200 && $status < 300);
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Get uploaded file path
 */
function getUploadPath($filename) {
    $uploadDir = __DIR__ . '/../uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    return $uploadDir . $filename;
}

/**
 * Get public URL for uploaded file
 */
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

/**
 * Validate and sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Log error to file
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