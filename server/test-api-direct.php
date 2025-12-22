<?php
/**
 * DIRECT API TEST - NO AUTHENTICATION
 * Save as: server/test-api-direct.php
 * Run: http://localhost:8080/server/test-api-direct.php
 * This tests if the SQL queries work correctly
 */

header('Content-Type: application/json; charset=UTF-8');

$host = "localhost";
$db_name = "sinath_travels";
$username = "root";
$password = "";

try {
    $conn = new PDO(
        "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4",
        $username,
        $password
    );
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "TESTING EXACT API QUERIES...\n\n";
    
    // Test 1: Package stats (exact same query as dashboard API)
    echo "=== PACKAGE QUERY ===\n";
    $packageQuery = "SELECT 
        COUNT(*) as total_packages,
        COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) as active_packages,
        COALESCE(SUM(CASE WHEN category = 'tour' THEN 1 ELSE 0 END), 0) as tour_packages,
        COALESCE(SUM(CASE WHEN category = 'visa' THEN 1 ELSE 0 END), 0) as visa_packages,
        COALESCE(SUM(CASE WHEN category = 'ticket' THEN 1 ELSE 0 END), 0) as ticket_packages,
        COALESCE(SUM(CASE WHEN category = 'offer' THEN 1 ELSE 0 END), 0) as offer_packages
    FROM packages";
    
    $stmt = $conn->query($packageQuery);
    $rawPackageStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Raw result:\n";
    echo json_encode($rawPackageStats, JSON_PRETTY_PRINT) . "\n\n";
    
    // Convert to integers (same as API)
    $packageData = [];
    foreach ($rawPackageStats as $key => $value) {
        $packageData[$key] = (int)($value ?? 0);
    }
    
    echo "After integer conversion:\n";
    echo json_encode($packageData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 2: Inquiry stats (exact same query as dashboard API)
    echo "=== INQUIRY QUERY ===\n";
    $inquiryQuery = "SELECT 
        COUNT(*) as total_inquiries,
        COALESCE(SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END), 0) as new_inquiries,
        COALESCE(SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END), 0) as read_inquiries,
        COALESCE(SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END), 0) as replied_inquiries
    FROM inquiries";
    
    $stmt = $conn->query($inquiryQuery);
    $rawInquiryStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Raw result:\n";
    echo json_encode($rawInquiryStats, JSON_PRETTY_PRINT) . "\n\n";
    
    // Convert to integers (same as API)
    $inquiryData = [];
    foreach ($rawInquiryStats as $key => $value) {
        $inquiryData[$key] = (int)($value ?? 0);
    }
    
    echo "After integer conversion:\n";
    echo json_encode($inquiryData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 3: Final API response format
    echo "=== FINAL API RESPONSE FORMAT ===\n";
    $response = [
        'success' => true,
        'data' => [
            'packages' => $packageData,
            'inquiries' => $inquiryData
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 4: Check if data exists
    echo "=== DATA CHECK ===\n";
    if ($packageData['total_packages'] > 0) {
        echo "✅ Packages found: " . $packageData['total_packages'] . "\n";
    } else {
        echo "❌ No packages found!\n";
    }
    
    if ($inquiryData['total_inquiries'] > 0) {
        echo "✅ Inquiries found: " . $inquiryData['total_inquiries'] . "\n";
    } else {
        echo "⚠️ No inquiries found (this is OK if none submitted yet)\n";
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>