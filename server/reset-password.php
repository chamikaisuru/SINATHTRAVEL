<?php
/**
 * RESET ADMIN PASSWORD
 * Save as: server/reset-password.php
 * Run ONCE: http://localhost:8080/server/reset-password.php
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
    
    // Generate NEW password hash for 'admin123'
    $newPassword = 'admin123';
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    
    echo "Generated hash: " . $newHash . "\n\n";
    
    // Update admin user
    $stmt = $conn->prepare("UPDATE admin_users SET password = :password WHERE username = 'admin'");
    $stmt->bindParam(':password', $newHash);
    $stmt->execute();
    
    // Verify it worked
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test the new hash
    $testVerify = password_verify($newPassword, $admin['password']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successful!',
        'results' => [
            'username' => 'admin',
            'new_password' => $newPassword,
            'new_hash' => substr($newHash, 0, 30) . '...',
            'verification_test' => $testVerify ? '✅ WORKING' : '❌ FAILED',
            'rows_updated' => $stmt->rowCount()
        ],
        'instructions' => [
            '1. Password has been reset to: admin123',
            '2. Go to login page and try again',
            '3. Delete this reset-password.php file after success'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>