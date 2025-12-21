<?php
/**
 * TEST DASHBOARD DATA
 * Save as: server/test-dashboard.php
 * Run: http://localhost:8080/server/test-dashboard.php
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
    
    // Test 1: Check packages
    echo "=== PACKAGES DATA ===\n\n";
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM packages");
    $totalPackages = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as active FROM packages WHERE status = 'active'");
    $activePackages = $stmt->fetch()['active'];
    
    $stmt = $conn->query("SELECT * FROM packages LIMIT 5");
    $samplePackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total Packages: $totalPackages\n";
    echo "Active Packages: $activePackages\n";
    echo "Sample Packages:\n";
    foreach ($samplePackages as $pkg) {
        echo "  - ID: {$pkg['id']}, Title: {$pkg['title_en']}, Status: {$pkg['status']}\n";
    }
    
    echo "\n=== INQUIRIES DATA ===\n\n";
    
    // Test 2: Check inquiries
    $stmt = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
        FROM inquiries");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total Inquiries: {$stats['total']}\n";
    echo "New: {$stats['new_count']}\n";
    echo "Read: {$stats['read_count']}\n";
    echo "Replied: {$stats['replied_count']}\n";
    
    $stmt = $conn->query("SELECT * FROM inquiries LIMIT 3");
    $sampleInquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nSample Inquiries:\n";
    foreach ($sampleInquiries as $inq) {
        echo "  - ID: {$inq['id']}, Name: {$inq['name']}, Status: {$inq['status']}\n";
    }
    
    echo "\n=== API RESPONSE FORMAT ===\n\n";
    
    // Test 3: Check what API returns
    $response = [
        'success' => true,
        'packages' => [
            'total' => $totalPackages,
            'active' => $activePackages,
            'samples' => $samplePackages
        ],
        'inquiries' => [
            'stats' => $stats,
            'samples' => $sampleInquiries
        ],
        'message' => '✅ Dashboard data ready!'
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>