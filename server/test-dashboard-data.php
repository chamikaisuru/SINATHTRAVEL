<?php
/**
 * TEST DASHBOARD DATA
 * Save as: server/test-dashboard-data.php
 * Run: http://localhost:8080/server/test-dashboard-data.php
 */

header('Content-Type: text/html; charset=utf-8');

$host = "localhost";
$db_name = "sinath_travels";
$username = "root";
$password = "";

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Data Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
        .section { margin: 20px 0; padding: 20px; background: #2a2a2a; border-radius: 8px; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
        .warning { color: #fbbf24; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #444; }
        th { background: #333; }
        pre { background: #1a1a1a; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>

<h1>📊 Dashboard Data Test</h1>

<?php

try {
    $conn = new PDO(
        "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4",
        $username,
        $password
    );
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '<div class="section success">✅ Database connection successful</div>';
    
    // TEST 1: Packages
    echo '<div class="section">';
    echo '<h2>📦 PACKAGES TEST</h2>';
    
    $stmt = $conn->query("SELECT 
        COUNT(*) as total_packages,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_packages,
        SUM(CASE WHEN category = 'tour' THEN 1 ELSE 0 END) as tour_packages,
        SUM(CASE WHEN category = 'visa' THEN 1 ELSE 0 END) as visa_packages,
        SUM(CASE WHEN category = 'ticket' THEN 1 ELSE 0 END) as ticket_packages,
        SUM(CASE WHEN category = 'offer' THEN 1 ELSE 0 END) as offer_packages
    FROM packages");
    $packageStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>Metric</th><th>Value</th></tr>';
    foreach ($packageStats as $key => $value) {
        $displayValue = $value === null ? '0 (NULL)' : $value;
        echo "<tr><td>$key</td><td>$displayValue</td></tr>";
    }
    echo '</table>';
    
    // Show sample packages
    $stmt = $conn->query("SELECT id, title_en, category, status FROM packages LIMIT 5");
    $samplePackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($samplePackages) > 0) {
        echo '<h3>Sample Packages:</h3>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Title</th><th>Category</th><th>Status</th></tr>';
        foreach ($samplePackages as $pkg) {
            echo "<tr><td>{$pkg['id']}</td><td>{$pkg['title_en']}</td><td>{$pkg['category']}</td><td>{$pkg['status']}</td></tr>";
        }
        echo '</table>';
    } else {
        echo '<div class="warning">⚠️ No packages found in database</div>';
    }
    
    echo '</div>';
    
    // TEST 2: Inquiries
    echo '<div class="section">';
    echo '<h2>📧 INQUIRIES TEST</h2>';
    
    $stmt = $conn->query("SELECT 
        COUNT(*) as total_inquiries,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_inquiries,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_inquiries,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_inquiries
    FROM inquiries");
    $inquiryStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>Metric</th><th>Value</th></tr>';
    foreach ($inquiryStats as $key => $value) {
        $displayValue = $value === null ? '0 (NULL)' : $value;
        echo "<tr><td>$key</td><td>$displayValue</td></tr>";
    }
    echo '</table>';
    
    // Show sample inquiries
    $stmt = $conn->query("SELECT id, name, email, status, created_at FROM inquiries LIMIT 5");
    $sampleInquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($sampleInquiries) > 0) {
        echo '<h3>Sample Inquiries:</h3>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Date</th></tr>';
        foreach ($sampleInquiries as $inq) {
            echo "<tr><td>{$inq['id']}</td><td>{$inq['name']}</td><td>{$inq['email']}</td><td>{$inq['status']}</td><td>{$inq['created_at']}</td></tr>";
        }
        echo '</table>';
    } else {
        echo '<div class="warning">⚠️ No inquiries found in database</div>';
    }
    
    echo '</div>';
    
    // TEST 3: API Response Format
    echo '<div class="section">';
    echo '<h2>🔧 API RESPONSE FORMAT</h2>';
    
    $apiResponse = [
        'success' => true,
        'data' => [
            'packages' => $packageStats,
            'inquiries' => $inquiryStats
        ]
    ];
    
    echo '<pre>' . json_encode($apiResponse, JSON_PRETTY_PRINT) . '</pre>';
    echo '</div>';
    
    // TEST 4: Add Sample Data (if empty)
    if ($packageStats['total_packages'] == 0) {
        echo '<div class="section warning">';
        echo '<h2>⚠️ Database is Empty - Adding Sample Data</h2>';
        
        // Add sample packages
        $stmt = $conn->prepare("INSERT INTO packages (category, title_en, description_en, price, duration, status) VALUES 
            ('tour', 'Sri Lanka Heritage Tour', 'Explore ancient wonders', 500.00, '5 Days', 'active'),
            ('tour', 'Dubai Shopping Festival', 'Luxury shopping experience', 800.00, '4 Days', 'active'),
            ('visa', 'Dubai Visa Service', 'Hassle-free visa processing', 150.00, '3-5 Days', 'active')");
        $stmt->execute();
        
        echo '<p class="success">✅ Added 3 sample packages</p>';
        
        // Add sample inquiry
        $stmt = $conn->prepare("INSERT INTO inquiries (name, email, phone, message, status) VALUES 
            ('John Doe', 'john@example.com', '0771234567', 'Test inquiry message', 'new')");
        $stmt->execute();
        
        echo '<p class="success">✅ Added 1 sample inquiry</p>';
        echo '<p><a href="">Refresh this page</a> to see the updated data</p>';
        echo '</div>';
    }
    
    echo '<div class="section success">';
    echo '<h2>✅ NEXT STEPS</h2>';
    echo '<ol>';
    echo '<li>If you see data above, your database is working correctly</li>';
    echo '<li>Make sure your API file is at: <code>server/api/admin/dashboard.php</code></li>';
    echo '<li>Try accessing: <code>http://localhost:8080/server/api/admin/dashboard.php</code></li>';
    echo '<li>Check browser console for any JavaScript errors</li>';
    echo '<li>Verify you\'re logged in to the admin panel</li>';
    echo '</ol>';
    echo '</div>';
    
} catch(PDOException $e) {
    echo '<div class="section error">';
    echo '<h2>❌ Database Error</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '</div>';
}

?>

</body>
</html>