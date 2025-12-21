<?php
/**
 * TEST DATABASE CONNECTION
 * Save as: server/test-db.php
 * Run: http://localhost:8080/server/test-db.php
 */

header('Content-Type: application/json');

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
    
    // Test database connection
    $stmt = $conn->query("SELECT 1");
    
    // Check if admin_users table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'admin_users'");
    $tableExists = $stmt->rowCount() > 0;
    
    // Count admin users
    $adminCount = 0;
    if ($tableExists) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM admin_users");
        $adminCount = $stmt->fetch()['count'];
    }
    
    // Check if default admin exists
    $defaultAdmin = null;
    if ($tableExists) {
        $stmt = $conn->prepare("SELECT username, email, role FROM admin_users WHERE username = 'admin'");
        $stmt->execute();
        $defaultAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful!',
        'database' => $db_name,
        'admin_users_table_exists' => $tableExists,
        'admin_count' => $adminCount,
        'default_admin' => $defaultAdmin,
        'test_query' => 'OK'
    ], JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage(),
        'error_code' => $e->getCode()
    ], JSON_PRETTY_PRINT);
}
?>