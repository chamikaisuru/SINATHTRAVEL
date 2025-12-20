<?php
/**
 * Admin Authentication API
 * Handles login, logout, and session management
 */

// CORS headers - MUST be FIRST before any other output
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS preflight immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Start session AFTER CORS headers
session_start();

// Include database connection
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'POST':
            $action = isset($_GET['action']) ? $_GET['action'] : 'login';
            if ($action === 'login') {
                handleLogin($db);
            } elseif ($action === 'logout') {
                handleLogout($db);
            } else {
                sendResponse(400, null, 'Invalid action');
            }
            break;
        
        case 'GET':
            handleCheckAuth($db);
            break;
        
        default:
            sendResponse(405, null, 'Method not allowed');
    }
} catch(Exception $e) {
    error_log("Admin Auth Error: " . $e->getMessage());
    sendResponse(500, null, 'Internal server error: ' . $e->getMessage());
}

/**
 * POST: Login
 */
function handleLogin($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validate input
    if (empty($data['username']) || empty($data['password'])) {
        sendResponse(400, null, 'Username and password required');
    }
    
    // Get admin user from database
    $query = "SELECT * FROM admin_users WHERE username = :username AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $data['username']);
    $stmt->execute();
    
    $admin = $stmt->fetch();
    
    // Verify credentials
    if (!$admin || !password_verify($data['password'], $admin['password'])) {
        sendResponse(401, null, 'Invalid credentials');
    }
    
    // Create session ID
    $sessionId = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Insert session into database
    $query = "INSERT INTO admin_sessions (id, admin_id, ip_address, user_agent, expires_at) 
              VALUES (:id, :admin_id, :ip, :user_agent, :expires_at)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $sessionId);
    $stmt->bindParam(':admin_id', $admin['id']);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt->bindParam(':ip', $ip);
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $stmt->bindParam(':user_agent', $userAgent);
    $stmt->bindParam(':expires_at', $expiresAt);
    $stmt->execute();
    
    // Update last login timestamp
    $query = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $admin['id']);
    $stmt->execute();
    
    // Set session variables
    $_SESSION['admin_session_id'] = $sessionId;
    $_SESSION['admin_id'] = $admin['id'];
    
    // Return success response
    sendResponse(200, [
        'token' => $sessionId,
        'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'full_name' => $admin['full_name'],
            'role' => $admin['role']
        ]
    ], 'Login successful');
}

/**
 * POST: Logout
 */
function handleLogout($db) {
    $sessionId = $_SESSION['admin_session_id'] ?? null;
    
    if ($sessionId) {
        // Delete session from database
        $query = "DELETE FROM admin_sessions WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $sessionId);
        $stmt->execute();
    }
    
    // Destroy PHP session
    session_destroy();
    
    sendResponse(200, null, 'Logout successful');
}

/**
 * GET: Check authentication status
 */
function handleCheckAuth($db) {
    $sessionId = $_SESSION['admin_session_id'] ?? null;
    
    if (!$sessionId) {
        sendResponse(401, null, 'Not authenticated');
    }
    
    // Verify session exists and is valid
    $query = "SELECT au.* FROM admin_users au 
              JOIN admin_sessions s ON au.id = s.admin_id 
              WHERE s.id = :session_id AND s.expires_at > NOW() AND au.status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':session_id', $sessionId);
    $stmt->execute();
    
    $admin = $stmt->fetch();
    
    if (!$admin) {
        session_destroy();
        sendResponse(401, null, 'Session expired');
    }
    
    sendResponse(200, [
        'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'full_name' => $admin['full_name'],
            'role' => $admin['role']
        ]
    ]);
}

/**
 * Verify admin authentication
 * Use this function in other admin API files
 */
function verifyAdminAuth($db) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $sessionId = $_SESSION['admin_session_id'] ?? null;
    
    if (!$sessionId) {
        sendResponse(401, null, 'Authentication required');
    }
    
    $query = "SELECT au.* FROM admin_users au 
              JOIN admin_sessions s ON au.id = s.admin_id 
              WHERE s.id = :session_id AND s.expires_at > NOW() AND au.status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':session_id', $sessionId);
    $stmt->execute();
    
    $admin = $stmt->fetch();
    
    if (!$admin) {
        sendResponse(401, null, 'Invalid or expired session');
    }
    
    return $admin;
}