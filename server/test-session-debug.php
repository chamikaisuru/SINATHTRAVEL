<?php
/**
 * SESSION DEBUG TEST
 * Save as: server/test-session-debug.php
 * Run: http://localhost:8080/server/test-session-debug.php
 * Run this AFTER logging into admin panel
 */

session_start();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
        .section { margin: 20px 0; padding: 20px; background: #2a2a2a; border-radius: 8px; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
        .warning { color: #fbbf24; }
        pre { background: #1a1a1a; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border: 1px solid #444; }
        th { background: #333; }
    </style>
</head>
<body>

<h1>🔍 Session Debug Test</h1>

<div class="section">
    <h2>📋 Current Session Info</h2>
    <table>
        <tr><th>Property</th><th>Value</th></tr>
        <tr>
            <td>Session ID</td>
            <td><code><?php echo session_id(); ?></code></td>
        </tr>
        <tr>
            <td>Admin Session ID</td>
            <td>
                <?php 
                if (isset($_SESSION['admin_session_id'])) {
                    echo '<span class="success">✅ ' . $_SESSION['admin_session_id'] . '</span>';
                } else {
                    echo '<span class="error">❌ NOT SET</span>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>Admin ID</td>
            <td>
                <?php 
                if (isset($_SESSION['admin_id'])) {
                    echo '<span class="success">✅ ' . $_SESSION['admin_id'] . '</span>';
                } else {
                    echo '<span class="error">❌ NOT SET</span>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>Admin Username</td>
            <td>
                <?php 
                if (isset($_SESSION['admin_username'])) {
                    echo '<span class="success">✅ ' . $_SESSION['admin_username'] . '</span>';
                } else {
                    echo '<span class="error">❌ NOT SET</span>';
                }
                ?>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>🔐 Full Session Data</h2>
    <pre><?php print_r($_SESSION); ?></pre>
</div>

<?php
if (isset($_SESSION['admin_session_id'])) {
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
        
        // Check if session exists in database
        $sessionId = $_SESSION['admin_session_id'];
        $query = "SELECT s.*, au.username, au.email, au.status 
                  FROM admin_sessions s 
                  JOIN admin_users au ON s.admin_id = au.id 
                  WHERE s.id = :session_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->execute();
        
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo '<div class="section">';
        echo '<h2>💾 Database Session Info</h2>';
        
        if ($session) {
            echo '<table>';
            echo '<tr><th>Field</th><th>Value</th></tr>';
            foreach ($session as $key => $value) {
                if ($key === 'expires_at') {
                    $isExpired = strtotime($value) < time();
                    $class = $isExpired ? 'error' : 'success';
                    $status = $isExpired ? '❌ EXPIRED' : '✅ VALID';
                    echo "<tr><td>$key</td><td class='$class'>$value ($status)</td></tr>";
                } else {
                    echo "<tr><td>$key</td><td>$value</td></tr>";
                }
            }
            echo '</table>';
            
            // Check if session is valid
            if (strtotime($session['expires_at']) > time() && $session['status'] === 'active') {
                echo '<div class="success" style="margin-top: 20px;">';
                echo '<h3>✅ SESSION IS VALID</h3>';
                echo '<p>You should be able to access the dashboard API</p>';
                echo '</div>';
            } else {
                echo '<div class="error" style="margin-top: 20px;">';
                echo '<h3>❌ SESSION PROBLEM</h3>';
                if (strtotime($session['expires_at']) < time()) {
                    echo '<p>Session has expired. Please login again.</p>';
                }
                if ($session['status'] !== 'active') {
                    echo '<p>Admin user is not active.</p>';
                }
                echo '</div>';
            }
        } else {
            echo '<div class="error">';
            echo '<h3>❌ SESSION NOT FOUND IN DATABASE</h3>';
            echo '<p>Your session ID exists in PHP session but not in database.</p>';
            echo '<p>This means you need to login again.</p>';
            echo '</div>';
        }
        echo '</div>';
        
    } catch(PDOException $e) {
        echo '<div class="section error">';
        echo '<h2>❌ Database Error</h2>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '</div>';
    }
} else {
    echo '<div class="section error">';
    echo '<h2>❌ NOT LOGGED IN</h2>';
    echo '<p>No admin session found. Please login first at:</p>';
    echo '<p><a href="http://localhost:5000/admin/login" style="color: #60a5fa;">http://localhost:5000/admin/login</a></p>';
    echo '</div>';
}
?>

<div class="section">
    <h2>📝 Next Steps</h2>
    <ol>
        <li><strong>If session is valid:</strong> The dashboard should work. Check browser console for errors.</li>
        <li><strong>If session expired:</strong> Login again at <code>http://localhost:5000/admin/login</code></li>
        <li><strong>If session not in database:</strong> Clear cookies and login again</li>
        <li><strong>After login:</strong> Come back to this page to verify session</li>
    </ol>
</div>

</body>
</html>