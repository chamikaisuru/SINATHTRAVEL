<?php
/**
 * PUBLIC Inquiries API
 * Handles contact form submissions from the frontend
 */
require_once '../config/database.php';
enableCORS();

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);
    
    if (empty($data)) {
        // Fallback to $_POST if not JSON
        $data = $_POST;
    }
    
    // Sanitize and Validate
    $name = sanitizeInput($data['name'] ?? '');
    $email = sanitizeInput($data['email'] ?? '');
    $phone = sanitizeInput($data['phone'] ?? '');
    $message = sanitizeInput($data['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        sendResponse(400, null, 'All fields are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(400, null, 'Invalid email format');
    }
    
    try {
        $query = "INSERT INTO inquiries (name, email, phone, message, status, created_at) 
                  VALUES (:name, :email, :phone, :message, 'new', NOW())";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':message', $message);
        
        if ($stmt->execute()) {
            $newId = $db->lastInsertId();
            
            // ✅ Send Email Notifications
            require_once '../config/email.php';
            
            $inquiryData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message
            ];
            
            // Send notification to admin
            $adminEmailSent = sendInquiryNotification($inquiryData);
            if ($adminEmailSent) {
                error_log("✅ Admin notification email sent for inquiry #{$newId}");
            } else {
                error_log("⚠️ Failed to send admin notification for inquiry #{$newId}");
            }
            
            // Send thank you email to customer
            $customerEmailSent = sendThankYouEmail($inquiryData);
            if ($customerEmailSent) {
                error_log("✅ Thank you email sent to customer: {$email}");
            } else {
                error_log("⚠️ Failed to send thank you email to: {$email}");
            }
            
            sendResponse(201, [
                'id' => $newId, 
                'message' => 'Inquiry submitted successfully. We will contact you soon!',
                'email_sent' => $adminEmailSent && $customerEmailSent
            ]);
        } else {
            sendResponse(500, null, 'Failed to save inquiry');
        }
    } catch(PDOException $e) {
        error_log("Inquiry Error: " . $e->getMessage());
        sendResponse(500, null, 'Database error');
    }
    
} else {
    // Allow options (already handled by enableCORS, but good to be explicit for 405)
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    sendResponse(405, null, 'Method not allowed. Only POST is supported.');
}
?>
