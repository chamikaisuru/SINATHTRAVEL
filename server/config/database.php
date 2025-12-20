<?php
/**
 * Database Configuration
 * Using PDO for secure database connections
 */

class Database {
    // Database credentials
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
 * Smart CORS - Development සහ Production දෙකටම වැඩ කරනවා
 */
function enableCORS() {
    // Allowed origins list (production domains)
    $allowedOrigins = [
        'https://www.sinathtravels.com',
        'https://sinathtravels.com',
        'https://yourdomain.com',  // ඔයාගේ live domain එක දාන්න
    ];
    
    // Check if we're in development mode
    $isDevelopment = (
        isset($_SERVER['HTTP_HOST']) && 
        (
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
            strpos($_SERVER['HTTP_HOST'], '192.168.') !== false ||
            strpos($_SERVER['HTTP_HOST'], '10.0.') !== false
        )
    );
    
    // Get request origin
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    if ($isDevelopment) {
        // Development: Allow all origins
        header("Access-Control-Allow-Origin: *");
    } else {
        // Production: Allow only specific domains
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            // If origin not in list, allow same domain
            $currentDomain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
                           . "://" . $_SERVER['HTTP_HOST'];
            header("Access-Control-Allow-Origin: $currentDomain");
        }
    }
    
    // Common headers for both dev and production
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 3600");

    // Handle preflight OPTIONS request
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
    header('Content-Type: application/json');
    
    $response = [];
    if ($message !== null) {
        $response['message'] = $message;
    }
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Get uploaded file path
 */
function getUploadPath($filename) {
    return __DIR__ . '/../uploads/' . $filename;
}

/**
 * Get public URL for uploaded file
 */
function getUploadUrl($filename) {
    // Smart URL generation for dev/production
    if (isset($_SERVER['HTTP_HOST'])) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        // Check if we're in a subdirectory
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = str_replace('/api', '', $scriptPath);
        
        return "$protocol://$host$basePath/uploads/$filename";
    }
    
    // Fallback for local development
    return '/server/uploads/' . $filename;
}