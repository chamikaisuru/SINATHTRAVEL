<?php
/**
 * COMPLETE PACKAGES DEBUG TOOL
 * Save as: server/debug-packages.php
 * Run: http://localhost:8080/server/debug-packages.php
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=utf-8');

$host = "localhost";
$db_name = "sinath_travels";
$username = "root";
$password = "";

?>
<!DOCTYPE html>
<html>
<head>
    <title>Packages Debug Tool</title>
    <style>
        body { 
            font-family: 'Courier New', monospace; 
            padding: 20px; 
            background: #0a0a0a; 
            color: #00ff00;
            line-height: 1.6;
        }
        .section { 
            margin: 20px 0; 
            padding: 20px; 
            background: #1a1a1a; 
            border: 2px solid #00ff00;
            border-radius: 8px; 
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffff00; }
        .info { color: #00aaff; }
        pre { 
            background: #000; 
            padding: 15px; 
            border: 1px solid #333;
            border-radius: 4px; 
            overflow-x: auto;
            color: #00ff00;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
        }
        th, td { 
            padding: 10px; 
            text-align: left; 
            border: 1px solid #333; 
        }
        th { 
            background: #222; 
            color: #00ff00;
            font-weight: bold;
        }
        td { background: #111; }
        h1 { 
            color: #00ff00; 
            text-shadow: 0 0 10px #00ff00;
            font-size: 2em;
            text-align: center;
            border-bottom: 2px solid #00ff00;
            padding-bottom: 10px;
        }
        h2 { 
            color: #00aaff; 
            border-bottom: 1px solid #00aaff;
            padding-bottom: 5px;
        }
        .highlight {
            background: #ffff00;
            color: #000;
            padding: 2px 5px;
            border-radius: 3px;
        }
        code {
            background: #222;
            padding: 2px 6px;
            border-radius: 3px;
            color: #ff00ff;
        }
    </style>
</head>
<body>

<h1>📦 PACKAGES API DEBUG TOOL</h1>

<?php

try {
    $conn = new PDO(
        "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4",
        $username,
        $password
    );
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '<div class="section success">';
    echo '<h2>✅ Step 1: Database Connection</h2>';
    echo '<p>Successfully connected to database: <code>' . $db_name . '</code></p>';
    echo '</div>';
    
    // =====================================================
    // STEP 2: Check if packages exist
    // =====================================================
    echo '<div class="section">';
    echo '<h2>📊 Step 2: Database Package Count</h2>';
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM packages");
    $totalPackages = $stmt->fetch()['total'];
    
    if ($totalPackages == 0) {
        echo '<p class="error">❌ NO PACKAGES IN DATABASE!</p>';
        echo '<p>Adding sample packages now...</p>';
        
        $conn->exec("INSERT INTO packages (category, title_en, description_en, price, duration, status) VALUES 
            ('tour', 'Sample Package 1', 'This is a test package', 500.00, '5 Days', 'active'),
            ('tour', 'Sample Package 2', 'Another test package', 800.00, '3 Days', 'active'),
            ('visa', 'Visa Service Test', 'Visa processing service', 150.00, '3-5 Days', 'active')");
        
        $stmt = $conn->query("SELECT COUNT(*) as total FROM packages");
        $totalPackages = $stmt->fetch()['total'];
        
        echo '<p class="success">✅ Added 3 sample packages</p>';
    }
    
    echo '<p class="success">Total packages in database: <span class="highlight">' . $totalPackages . '</span></p>';
    echo '</div>';
    
    // =====================================================
    // STEP 3: Show all packages
    // =====================================================
    echo '<div class="section">';
    echo '<h2>📋 Step 3: All Packages in Database</h2>';
    
    $stmt = $conn->query("SELECT * FROM packages ORDER BY created_at DESC");
    $allPackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>ID</th><th>Title</th><th>Category</th><th>Price</th><th>Status</th><th>Created</th></tr>';
    foreach ($allPackages as $pkg) {
        $statusClass = $pkg['status'] === 'active' ? 'success' : 'warning';
        echo "<tr>";
        echo "<td>{$pkg['id']}</td>";
        echo "<td>{$pkg['title_en']}</td>";
        echo "<td>{$pkg['category']}</td>";
        echo "<td>\${$pkg['price']}</td>";
        echo "<td class='$statusClass'>{$pkg['status']}</td>";
        echo "<td>{$pkg['created_at']}</td>";
        echo "</tr>";
    }
    echo '</table>';
    echo '</div>';
    
    // =====================================================
    // STEP 4: Test the exact API query
    // =====================================================
    echo '<div class="section">';
    echo '<h2>🔍 Step 4: Test Exact API Query</h2>';
    
    $query = "SELECT * FROM packages WHERE 1=1 ORDER BY created_at DESC";
    echo '<p>Query: <code>' . htmlspecialchars($query) . '</code></p>';
    
    $stmt = $conn->query($query);
    $apiPackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<p class="success">Query returned: <span class="highlight">' . count($apiPackages) . '</span> packages</p>';
    echo '<pre>' . json_encode($apiPackages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
    echo '</div>';
    
    // =====================================================
    // STEP 5: Test API response format
    // =====================================================
    echo '<div class="section">';
    echo '<h2>📤 Step 5: API Response Format</h2>';
    
    $apiResponse = [
        'success' => true,
        'data' => $apiPackages,
        'message' => 'Packages retrieved successfully'
    ];
    
    echo '<p>This is what the API should return:</p>';
    echo '<pre>' . json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
    echo '</div>';
    
    // =====================================================
    // STEP 6: Test actual API endpoint
    // =====================================================
    echo '<div class="section">';
    echo '<h2>🌐 Step 6: Test Actual API Endpoint</h2>';
    
    $sessionId = $_SESSION['admin_session_id'] ?? null;
    
    if ($sessionId) {
        echo '<p class="success">✅ Session ID found: <code>' . $sessionId . '</code></p>';
        
        // Verify session in database
        $stmt = $conn->prepare("SELECT * FROM admin_sessions WHERE id = :id AND expires_at > NOW()");
        $stmt->bindParam(':id', $sessionId);
        $stmt->execute();
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($session) {
            echo '<p class="success">✅ Session is valid</p>';
            echo '<p class="info">Now test the API endpoint:</p>';
            echo '<p><a href="http://localhost:8080/server/api/admin/packages.php" target="_blank" style="color: #00aaff;">http://localhost:8080/server/api/admin/packages.php</a></p>';
        } else {
            echo '<p class="error">❌ Session expired or invalid</p>';
            echo '<p class="warning">You need to login first at: <a href="http://localhost:5000/admin/login" style="color: #ffff00;">Admin Login</a></p>';
        }
    } else {
        echo '<p class="error">❌ No session found - you are not logged in</p>';
        echo '<p class="warning">Login first at: <a href="http://localhost:5000/admin/login" style="color: #ffff00;">Admin Login</a></p>';
    }
    echo '</div>';
    
    // =====================================================
    // STEP 7: Frontend debug instructions
    // =====================================================
    echo '<div class="section">';
    echo '<h2>💻 Step 7: Frontend Debugging</h2>';
    echo '<ol>';
    echo '<li>Open <code>http://localhost:5000/admin/packages</code></li>';
    echo '<li>Open browser console (F12 → Console tab)</li>';
    echo '<li>Look for these messages:';
    echo '<ul>';
    echo '<li class="success">✅ <code>🔍 Fetching packages from API...</code></li>';
    echo '<li class="success">✅ <code>🔍 API returned: [...]</code></li>';
    echo '<li class="success">✅ <code>✅ Returning X packages</code></li>';
    echo '</ul>';
    echo '</li>';
    echo '<li>If you see errors, copy them and check the following:</li>';
    echo '<ul>';
    echo '<li>❌ <code>401 Unauthorized</code> → Login again</li>';
    echo '<li>❌ <code>Network Error</code> → Check if XAMPP is running</li>';
    echo '<li>❌ <code>CORS Error</code> → Check server/config/database.php</li>';
    echo '</ul>';
    echo '</ol>';
    echo '</div>';
    
    // =====================================================
    // STEP 8: Check API file exists
    // =====================================================
    echo '<div class="section">';
    echo '<h2>📁 Step 8: Check API Files</h2>';
    
    $apiFile = __DIR__ . '/api/admin/packages.php';
    $authFile = __DIR__ . '/api/admin/auth.php';
    
    echo '<table>';
    echo '<tr><th>File</th><th>Status</th><th>Path</th></tr>';
    
    echo '<tr>';
    echo '<td>packages.php</td>';
    if (file_exists($apiFile)) {
        echo '<td class="success">✅ EXISTS</td>';
    } else {
        echo '<td class="error">❌ MISSING</td>';
    }
    echo '<td><code>' . $apiFile . '</code></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>auth.php</td>';
    if (file_exists($authFile)) {
        echo '<td class="success">✅ EXISTS</td>';
    } else {
        echo '<td class="error">❌ MISSING</td>';
    }
    echo '<td><code>' . $authFile . '</code></td>';
    echo '</tr>';
    
    echo '</table>';
    echo '</div>';
    
    // =====================================================
    // STEP 9: Next actions
    // =====================================================
    echo '<div class="section info">';
    echo '<h2>🎯 Step 9: What To Do Next</h2>';
    echo '<ol>';
    echo '<li><strong>If you see packages above:</strong>';
    echo '<ul>';
    echo '<li>Database is working ✅</li>';
    echo '<li>SQL queries are working ✅</li>';
    echo '<li>Problem is in frontend or API authentication</li>';
    echo '</ul>';
    echo '</li>';
    echo '<li><strong>Test the API endpoint:</strong>';
    echo '<ul>';
    echo '<li>Make sure you are logged in to admin panel</li>';
    echo '<li>Click the API link in Step 6 above</li>';
    echo '<li>You should see JSON with packages</li>';
    echo '</ul>';
    echo '</li>';
    echo '<li><strong>Check browser console:</strong>';
    echo '<ul>';
    echo '<li>Open admin packages page</li>';
    echo '<li>Open console (F12)</li>';
    echo '<li>Look for error messages</li>';
    echo '<li>Copy any errors and send them to me</li>';
    echo '</ul>';
    echo '</li>';
    echo '</ol>';
    echo '</div>';
    
    // =====================================================
    // SUMMARY
    // =====================================================
    echo '<div class="section">';
    echo '<h2>📝 Summary</h2>';
    echo '<table>';
    echo '<tr><th>Check</th><th>Status</th></tr>';
    echo '<tr><td>Database Connection</td><td class="success">✅ OK</td></tr>';
    echo '<tr><td>Packages in Database</td><td class="' . ($totalPackages > 0 ? 'success' : 'error') . '">' . ($totalPackages > 0 ? '✅' : '❌') . ' ' . $totalPackages . ' packages</td></tr>';
    echo '<tr><td>API Files Exist</td><td class="' . (file_exists($apiFile) ? 'success' : 'error') . '">' . (file_exists($apiFile) ? '✅ YES' : '❌ NO') . '</td></tr>';
    echo '<tr><td>Admin Session</td><td class="' . ($sessionId ? 'success' : 'error') . '">' . ($sessionId ? '✅ Logged in' : '❌ Not logged in') . '</td></tr>';
    echo '</table>';
    echo '</div>';
    
} catch(PDOException $e) {
    echo '<div class="section error">';
    echo '<h2>❌ Database Error</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<p>Error Code: ' . $e->getCode() . '</p>';
    echo '</div>';
}

?>

<div class="section" style="border-color: #ffff00;">
    <h2 style="color: #ffff00;">⚠️ After Running This Test</h2>
    <p>Please share the following with me:</p>
    <ol>
        <li>How many packages you see in Step 2</li>
        <li>Whether the API link in Step 6 works</li>
        <li>Any errors from browser console (Step 7)</li>
        <li>Screenshot of the admin packages page</li>
    </ol>
</div>

</body>
</html>