<?php
/**
 * DIRECT PACKAGES TEST
 * Run: http://localhost:8080/server/test-packages-direct.php
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
    
    echo "=== DIRECT DATABASE QUERY ===\n\n";
    
    // Exact query from packages.php
    $query = "SELECT * FROM packages WHERE 1=1 ORDER BY created_at DESC";
    
    $stmt = $conn->query($query);
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total packages found: " . count($packages) . "\n\n";
    
    if (count($packages) === 0) {
        echo "❌ NO PACKAGES IN DATABASE!\n\n";
        
        // Add sample data
        echo "Adding sample packages...\n";
        
        $insertQuery = "INSERT INTO packages (category, title_en, description_en, price, duration, status) VALUES 
            ('tour', 'Test Package 1', 'This is a test package', 500.00, '5 Days', 'active'),
            ('tour', 'Test Package 2', 'Another test package', 800.00, '3 Days', 'active'),
            ('visa', 'Visa Service', 'Visa processing service', 150.00, '3-5 Days', 'active')";
        
        $conn->exec($insertQuery);
        
        // Query again
        $stmt = $conn->query($query);
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "✅ Added " . count($packages) . " packages\n\n";
    }
    
    echo "=== PACKAGES DATA ===\n";
    echo json_encode($packages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";
    
    echo "=== SENDRESPONSE FORMAT (what API returns) ===\n";
    $apiResponse = [
        'success' => true,
        'data' => $packages,
        'message' => 'Packages retrieved successfully'
    ];
    echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>