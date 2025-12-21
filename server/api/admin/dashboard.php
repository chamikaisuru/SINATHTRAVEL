<?php
require_once '../../config/database.php';
require_once './auth.php';

enableCORS();

$database = new Database();
$db = $database->getConnection();

try {
    $admin = verifyAdminAuth($db);
} catch(Exception $e) {
    exit;
}

try {
    // Package stats
    $stmt = $db->query("SELECT 
        COUNT(*) as total_packages,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_packages,
        SUM(CASE WHEN category = 'tour' THEN 1 ELSE 0 END) as tour_packages,
        SUM(CASE WHEN category = 'visa' THEN 1 ELSE 0 END) as visa_packages,
        SUM(CASE WHEN category = 'ticket' THEN 1 ELSE 0 END) as ticket_packages,
        SUM(CASE WHEN category = 'offer' THEN 1 ELSE 0 END) as offer_packages
    FROM packages");
    $packageStats = $stmt->fetch();
    
    // Inquiry stats
    $stmt = $db->query("SELECT 
        COUNT(*) as total_inquiries,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_inquiries,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_inquiries,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_inquiries
    FROM inquiries");
    $inquiryStats = $stmt->fetch();
    
    // Convert NULL to 0
    foreach ($packageStats as $key => $value) {
        $packageStats[$key] = (int)$value;
    }
    
    foreach ($inquiryStats as $key => $value) {
        $inquiryStats[$key] = (int)$value;
    }
    
    sendResponse(200, [
        'packages' => $packageStats,
        'inquiries' => $inquiryStats,
        'system_status' => [
            'database' => 'online',
            'api_server' => 'online',
            'image_uploads' => 'online',
            'email_service' => 'not_configured'
        ]
    ]);
    
} catch(Exception $e) {
    error_log("❌ Dashboard Error: " . $e->getMessage());
    sendResponse(500, null, 'Failed to fetch dashboard data');
}
?>