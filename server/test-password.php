<?php
/**
 * TEST PASSWORD VERIFICATION
 * Save as: server/test-password.php
 * Run: http://localhost:8080/server/test-password.php
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
    
    // Get admin user
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo json_encode([
            'success' => false,
            'message' => 'Admin user not found'
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Test password verification
    $testPassword = 'admin123';
    $storedHash = $admin['password'];
    
    $isValid = password_verify($testPassword, $storedHash);
    
    // Additional info
    $hashInfo = password_get_info($storedHash);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password test completed',
        'test_results' => [
            'username' => $admin['username'],
            'test_password' => $testPassword,
            'stored_hash' => substr($storedHash, 0, 30) . '...',
            'password_valid' => $isValid,
            'hash_algorithm' => $hashInfo['algoName'],
            'hash_options' => $hashInfo['options']
        ],
        'admin_status' => $admin['status'],
        'admin_role' => $admin['role'],
        'recommendation' => $isValid 
            ? '✅ Password verification works! Issue is elsewhere.' 
            : '❌ Password hash incorrect! Need to reset.'
    ], JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>