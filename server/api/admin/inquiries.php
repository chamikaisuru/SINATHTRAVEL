<?php
/**
 * COMPLETELY FIXED Admin Inquiries API
 */

require_once '../../config/database.php';
require_once './auth.php';

enableCORS();

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

error_log("📬 INQUIRIES API CALLED");
error_log("Method: " . $method);

// Verify admin authentication
try {
    $admin = verifyAdminAuth($db);
    error_log("✅ Admin verified in inquiries.php: " . $admin['username']);
} catch(Exception $e) {
    error_log("❌ Auth failed in inquiries.php");
    exit;
}

try {
    switch($method) {
        case 'GET':
            handleGet($db);
            break;
        
        case 'PUT':
            handlePut($db);
            break;
        
        case 'DELETE':
            handleDelete($db);
            break;
        
        default:
            sendResponse(405, null, 'Method not allowed');
    }
} catch(Exception $e) {
    error_log("❌ Inquiries Error: " . $e->getMessage());
    sendResponse(500, null, 'Internal server error');
}

/**
 * GET: Fetch inquiries with pagination and filtering
 */
function handleGet($db) {
    error_log("📬 handleGet inquiries called");
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        // Get single inquiry
        $query = "SELECT * FROM inquiries WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        sendResponse(200, $stmt->fetch());
    } else {
        // Get all inquiries with filters
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        
        $query = "SELECT * FROM inquiries WHERE 1=1";
        
        if ($status) {
            $query .= " AND status = :status";
        }
        
        if ($search) {
            $query .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR message LIKE :search)";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($query);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        if ($search) {
            $searchTerm = "%$search%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $inquiries = $stmt->fetchAll();
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM inquiries WHERE 1=1";
        
        if ($status) {
            $countQuery .= " AND status = :status";
        }
        
        if ($search) {
            $countQuery .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR message LIKE :search)";
        }
        
        $countStmt = $db->prepare($countQuery);
        
        if ($status) {
            $countStmt->bindParam(':status', $status);
        }
        
        if ($search) {
            $countStmt->bindParam(':search', $searchTerm);
        }
        
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];
        
        // Get status counts
        $statsQuery = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
                        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
                       FROM inquiries";
        $statsStmt = $db->prepare($statsQuery);
        $statsStmt->execute();
        $stats = $statsStmt->fetch();
        
        error_log("📬 Found " . count($inquiries) . " inquiries");
        error_log("📬 Stats: " . json_encode($stats));
        
        sendResponse(200, [
            'inquiries' => $inquiries,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'pages' => ceil($total / $limit)
            ],
            'stats' => $stats
        ]);
    }
}

/**
 * PUT: Update inquiry status
 */
function handlePut($db) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        sendResponse(400, null, 'Inquiry ID required');
    }
    
    $allowedStatuses = ['new', 'read', 'replied'];
    
    if (empty($data['status']) || !in_array($data['status'], $allowedStatuses)) {
        sendResponse(400, null, 'Valid status required (new, read, replied)');
    }
    
    $query = "UPDATE inquiries SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':status', $data['status']);
    
    if ($stmt->execute()) {
        sendResponse(200, null, 'Inquiry status updated successfully');
    } else {
        sendResponse(500, null, 'Failed to update inquiry');
    }
}

/**
 * DELETE: Delete inquiry
 */
function handleDelete($db) {
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        sendResponse(400, null, 'Inquiry ID required');
    }
    
    $query = "DELETE FROM inquiries WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    
    if ($stmt->execute()) {
        sendResponse(200, null, 'Inquiry deleted successfully');
    } else {
        sendResponse(500, null, 'Failed to delete inquiry');
    }
}
?>