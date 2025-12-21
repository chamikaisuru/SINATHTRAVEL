<?php
/**
 * Admin Authentication API
 * Handles login, logout, and session management
 */

// CRITICAL: Database config include කරන්න BEFORE any output
require_once '../../config/database.php';

// CRITICAL: CORS headers set කරන්න FIRST
enableCORS();

// Session start කරන්න CORS headers වලින් පස්සේ
if (session_status() === PHP_SESSION_NONE) {
    // Session cookie එක සඳහා secure settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'None');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

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
    
    // Debug logging
    error_log("Login attempt for username: " . ($data['username'] ?? 'none'));
    
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
    
    // Debug logging
    if (!$admin) {
        error_log("User not found: " . $data['username']);
    } else {
        error_log("User found: " . $admin['username']);
    }
    
    // Verify credentials
    if (!$admin || !password_verify($data['password'], $admin['password'])) {
        error_log("Invalid credentials for user: " . $data['username']);
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
    
    error_log("Login successful for user: " . $admin['username']);
    
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
        $query = "DELETE FROM admin_sessions WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $sessionId);
        $stmt->execute();
    }
    
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