<?php
/**
 * TEST API DATA
 * Save as: server/test-api.php
 * Run: http://localhost:8080/server/test-api.php
 */

header('Content-Type: application/json');

$host = "localhost";
$db_name = "sinath_travels";
$username = "root";
$password = "";

try {
    $conn = new PDO(
        "mysql:host=" . $host . ";dbname=" . $db_name,
        $username,
        $password
    );
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test packages
    $stmt = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN category = 'tour' THEN 1 ELSE 0 END) as tours,
        SUM(CASE WHEN category = 'visa' THEN 1 ELSE 0 END) as visas
        FROM packages");
    $packageStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test inquiries
    $stmt = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
        FROM inquiries");
    $inquiryStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get sample packages
    $stmt = $conn->query("SELECT * FROM packages LIMIT 5");
    $samplePackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get sample inquiries
    $stmt = $conn->query("SELECT * FROM inquiries LIMIT 5");
    $sampleInquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'packages' => [
            'stats' => $packageStats,
            'samples' => $samplePackages
        ],
        'inquiries' => [
            'stats' => $inquiryStats,
            'samples' => $sampleInquiries
        ],
        'message' => 'Database has data!'
    ], JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>