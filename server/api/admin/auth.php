<?php
/**
 * Admin Authentication API - COMPLETE FIXED VERSION
 * Fixes the issue where verifyAdminAuth was returning wrong data
 */

// STEP 1: Set CORS headers FIRST
require_once '../../config/database.php';
enableCORS();

// STEP 2: Configure session - FIXED FOR HTTP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_samesite', 'None');
    ini_set('session.cookie_secure', 1);
} else {
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', 0);
}

ini_set('session.cookie_path', '/');

// STEP 3: Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// STEP 4: Initialize database
$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

error_log("========== AUTH DEBUG ==========");
error_log("Method: " . $method);
error_log("Action: " . ($_GET['action'] ?? 'none'));

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
    error_log("❌ Auth Error: " . $e->getMessage());
    sendResponse(500, null, 'Internal server error: ' . $e->getMessage());
}

function handleLogin($db) {
    error_log("🔵 handleLogin called");
    
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);
    
    if (empty($data['username']) || empty($data['password'])) {
        sendResponse(400, null, 'Username and password required');
    }
    
    error_log("🔍 Looking for user: " . $data['username']);
    
    $query = "SELECT * FROM admin_users WHERE username = :username AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $data['username']);
    $stmt->execute();
    
    $admin = $stmt->fetch();
    
    if (!$admin) {
        error_log("❌ User not found: " . $data['username']);
        sendResponse(401, null, 'Invalid credentials');
    }
    
    if (!password_verify($data['password'], $admin['password'])) {
        error_log("❌ Invalid password");
        sendResponse(401, null, 'Invalid credentials');
    }
    
    error_log("✅ Password verified");
    
    // Create session
    $sessionId = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
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
    
    // Update last login
    $query = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $admin['id']);
    $stmt->execute();
    
    // Set session variables
    $_SESSION['admin_session_id'] = $sessionId;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    
    session_write_close();
    
    error_log("✅ Login successful for: " . $admin['username']);
    
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

function handleCheckAuth($db) {
    error_log("🔍 Checking auth...");
    
    $sessionId = $_SESSION['admin_session_id'] ?? null;
    
    if (!$sessionId) {
        error_log("❌ No session ID");
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
        error_log("❌ Session expired");
        session_destroy();
        sendResponse(401, null, 'Session expired');
    }
    
    error_log("✅ Auth check passed");
    
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
 * FIXED: Verify admin authentication
 * This function ONLY returns the admin user, it does NOT send a response
 */
function verifyAdminAuth($db) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    error_log("🔍 Verifying admin for API request...");
    
    $sessionId = $_SESSION['admin_session_id'] ?? null;
    
    if (!$sessionId) {
        error_log("❌ No session ID found in verifyAdminAuth");
        sendResponse(401, null, 'Authentication required');
        exit; // Only exit on failure
    }
    
    $query = "SELECT au.* FROM admin_users au 
              JOIN admin_sessions s ON au.id = s.admin_id 
              WHERE s.id = :session_id AND s.expires_at > NOW() AND au.status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':session_id', $sessionId);
    $stmt->execute();
    
    $admin = $stmt->fetch();
    
    if (!$admin) {
        error_log("❌ Invalid session in verifyAdminAuth");
        sendResponse(401, null, 'Invalid or expired session');
        exit; // Only exit on failure
    }
    
    error_log("✅ Admin verified: " . $admin['username']);
    
    // CRITICAL FIX: Just return the admin, don't send any response
    return $admin;
}
?>