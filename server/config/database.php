<?php
/**
 * Database Configuration
 * Using PDO for secure database connections
 */

class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "sinath_travels";
    private $username = "root"; // Change this to your MySQL username
    private $password = "";     // Change this to your MySQL password
    private $conn;

    /**
     * Get database connection
     * @return PDO
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
            
            // Return associative arrays by default
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }
}

/**
 * Enable CORS for React frontend
 * Call this at the beginning of each API endpoint
 */
function enableCORS() {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

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
 * @param string $filename
 * @return string
 */
function getUploadPath($filename) {
    return __DIR__ . '/../uploads/' . $filename;
}

/**
 * Get public URL for uploaded file
 * @param string $filename
 * @return string
 */
function getUploadUrl($filename) {
    // Adjust this based on your server configuration
    return '/server/uploads/' . $filename;
}