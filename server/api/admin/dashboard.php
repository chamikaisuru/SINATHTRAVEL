<?php
/**
 * COMPLETELY FIXED Admin Dashboard API
 * Replace: server/api/admin/dashboard.php
 */

// STEP 1: Start session FIRST (before anything else)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// STEP 2: Load dependencies
require_once '../../config/database.php';

// STEP 3: Enable CORS
enableCORS();

error_log("========== DASHBOARD API ==========");
error_log("Session ID: " . session_id());
error_log("Admin Session ID: " . ($_SESSION['admin_session_id'] ?? 'NONE'));
error_log("Admin ID: " . ($_SESSION['admin_id'] ?? 'NONE'));

// STEP 4: Initialize database
$database = new Database();
$db = $database->getConnection();

// STEP 5: Verify authentication
$sessionId = $_SESSION['admin_session_id'] ?? null;

if (!$sessionId) {
    error_log("❌ No session ID found in dashboard.php");
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated - please login',
        'debug' => [
            'session_id' => session_id(),
            'has_admin_session_id' => isset($_SESSION['admin_session_id']),
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

// Check if session is valid
try {
    $query = "SELECT au.* FROM admin_users au 
              JOIN admin_sessions s ON au.id = s.admin_id 
              WHERE s.id = :session_id AND s.expires_at > NOW() AND au.status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':session_id', $sessionId);
    $stmt->execute();
    
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        error_log("❌ Invalid or expired session in dashboard.php");
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Session expired - please login again'
        ]);
        exit;
    }
    
    error_log("✅ Admin verified: " . $admin['username']);
    
} catch(Exception $e) {
    error_log("❌ Auth check error: " . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication error'
    ]);
    exit;
}

// STEP 6: Fetch dashboard data
try {
    error_log("📊 Fetching dashboard data...");
    
    // Package stats with explicit conversion
    $packageQuery = "SELECT 
        COUNT(*) as total_packages,
        COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) as active_packages,
        COALESCE(SUM(CASE WHEN category = 'tour' THEN 1 ELSE 0 END), 0) as tour_packages,
        COALESCE(SUM(CASE WHEN category = 'visa' THEN 1 ELSE 0 END), 0) as visa_packages,
        COALESCE(SUM(CASE WHEN category = 'ticket' THEN 1 ELSE 0 END), 0) as ticket_packages,
        COALESCE(SUM(CASE WHEN category = 'offer' THEN 1 ELSE 0 END), 0) as offer_packages
    FROM packages";
    
    $stmt = $db->query($packageQuery);
    $packageStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("📦 Raw package stats: " . json_encode($packageStats));
    
    // Convert to integers
    $packageData = [];
    foreach ($packageStats as $key => $value) {
        $packageData[$key] = (int)($value ?? 0);
    }
    
    error_log("📦 Converted package stats: " . json_encode($packageData));
    
    // Inquiry stats with explicit conversion
    $inquiryQuery = "SELECT 
        COUNT(*) as total_inquiries,
        COALESCE(SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END), 0) as new_inquiries,
        COALESCE(SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END), 0) as read_inquiries,
        COALESCE(SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END), 0) as replied_inquiries
    FROM inquiries";
    
    $stmt = $db->query($inquiryQuery);
    $inquiryStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("📧 Raw inquiry stats: " . json_encode($inquiryStats));
    
    // Convert to integers
    $inquiryData = [];
    foreach ($inquiryStats as $key => $value) {
        $inquiryData[$key] = (int)($value ?? 0);
    }
    
    error_log("📧 Converted inquiry stats: " . json_encode($inquiryData));
    
    // Build response
    $response = [
        'success' => true,
        'data' => [
            'packages' => $packageData,
            'inquiries' => $inquiryData,
            'system_status' => [
                'database' => 'online',
                'api_server' => 'online',
                'session' => 'valid',
                'admin_user' => $admin['username']
            ]
        ],
        'message' => 'Dashboard data retrieved successfully'
    ];
    
    error_log("✅ Sending response: " . json_encode($response));
    
    http_response_code(200);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
    
} catch(Exception $e) {
    error_log("❌ Dashboard Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch dashboard data',
        'error' => $e->getMessage(),
        'debug' => [
            'admin' => $admin['username'] ?? 'unknown',
            'session_id' => $sessionId
        ]
    ]);
    exit;
}
?>