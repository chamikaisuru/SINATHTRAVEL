<?php
/**
 * Email Configuration for Sinath Travels
 * Uses Gmail SMTP with clickbee@gmail.com
 */

// Email Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_USERNAME', 'clickbee@gmail.com');
define('SMTP_PASSWORD', ''); // ⚠️ ADD YOUR APP PASSWORD HERE (Not your Gmail password!)

// Sender Details
define('FROM_EMAIL', 'clickbee@gmail.com');
define('FROM_NAME', 'Sinath Travels');

// Admin Email (where inquiries are sent)
define('ADMIN_EMAIL', 'clickbee@gmail.com');
define('ADMIN_NAME', 'Sinath Travels Admin');

/**
 * Send Email using PHP mail() function with Gmail-compatible headers
 */
function sendEmail($to, $toName, $subject, $htmlBody, $plainBody = '') {
    // If no plain text version, strip HTML
    if (empty($plainBody)) {
        $plainBody = strip_tags($htmlBody);
    }
    
    // Generate boundary for multipart email
    $boundary = md5(time());
    
    // Headers
    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
    $headers[] = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">";
    $headers[] = "Reply-To: " . FROM_EMAIL;
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    // Email body
    $message = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $plainBody . "\r\n\r\n";
    
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $htmlBody . "\r\n\r\n";
    
    $message .= "--{$boundary}--";
    
    // Send email
    $success = mail($to, $subject, $message, implode("\r\n", $headers));
    
    if ($success) {
        error_log("✅ Email sent to: {$to}");
        return true;
    } else {
        error_log("❌ Failed to send email to: {$to}");
        return false;
    }
}

/**
 * Send New Inquiry Notification to Admin
 */
function sendInquiryNotification($inquiryData) {
    $subject = "🔔 New Inquiry from {$inquiryData['name']}";
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .info-row { margin: 15px 0; padding: 15px; background: white; border-left: 4px solid #667eea; border-radius: 4px; }
            .label { font-weight: bold; color: #667eea; display: block; margin-bottom: 5px; }
            .message-box { background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; margin-top: 20px; }
            .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; color: #6b7280; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; font-size: 28px;'>✉️ New Inquiry Received</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>Sinath Travels Website</p>
            </div>
            <div class='content'>
                <p style='font-size: 16px; margin-bottom: 20px;'>You have received a new inquiry from your website:</p>
                
                <div class='info-row'>
                    <span class='label'>👤 Name:</span>
                    {$inquiryData['name']}
                </div>
                
                <div class='info-row'>
                    <span class='label'>📧 Email:</span>
                    <a href='mailto:{$inquiryData['email']}' style='color: #667eea; text-decoration: none;'>{$inquiryData['email']}</a>
                </div>
                
                <div class='info-row'>
                    <span class='label'>📱 Phone:</span>
                    <a href='tel:{$inquiryData['phone']}' style='color: #667eea; text-decoration: none;'>{$inquiryData['phone']}</a>
                </div>
                
                <div class='message-box'>
                    <span class='label' style='margin-bottom: 10px;'>💬 Message:</span>
                    <p style='margin: 0; white-space: pre-wrap;'>{$inquiryData['message']}</p>
                </div>
                
                <div class='footer'>
                    <p style='margin: 0;'>Received on: " . date('F j, Y \a\t g:i A') . "</p>
                    <p style='margin: 10px 0 0 0;'>🌐 Sinath Travels Admin Panel</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $plainBody = "New Inquiry from Sinath Travels Website\n\n" .
                 "Name: {$inquiryData['name']}\n" .
                 "Email: {$inquiryData['email']}\n" .
                 "Phone: {$inquiryData['phone']}\n\n" .
                 "Message:\n{$inquiryData['message']}\n\n" .
                 "Received on: " . date('F j, Y \a\t g:i A');
    
    return sendEmail(ADMIN_EMAIL, ADMIN_NAME, $subject, $htmlBody, $plainBody);
}

/**
 * Send Thank You Email to Customer
 */
function sendThankYouEmail($inquiryData) {
    $subject = "Thank You for Contacting Sinath Travels";
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; color: #6b7280; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; font-size: 28px;'>🙏 Thank You!</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>Sinath Travels</p>
            </div>
            <div class='content'>
                <p style='font-size: 16px;'>Dear {$inquiryData['name']},</p>
                
                <p>Thank you for contacting <strong>Sinath Travels</strong>! We have received your inquiry and our team will get back to you within 24 hours.</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;'>
                    <p style='margin: 0; color: #667eea; font-weight: bold;'>Your Message:</p>
                    <p style='margin: 10px 0 0 0; white-space: pre-wrap;'>{$inquiryData['message']}</p>
                </div>
                
                <p>If you have any urgent questions, feel free to call us directly at:</p>
                <p style='font-size: 18px; font-weight: bold; color: #667eea; margin: 10px 0;'>📞 {$inquiryData['phone']}</p>
                
                <div class='footer'>
                    <p style='margin: 0; font-weight: bold;'>Sinath Travels</p>
                    <p style='margin: 5px 0;'>Your Journey, Our Passion</p>
                    <p style='margin: 5px 0;'>📧 clickbee@gmail.com</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $plainBody = "Dear {$inquiryData['name']},\n\n" .
                 "Thank you for contacting Sinath Travels! We have received your inquiry and our team will get back to you within 24 hours.\n\n" .
                 "Your Message:\n{$inquiryData['message']}\n\n" .
                 "Best Regards,\nSinath Travels Team\n" .
                 "Email: clickbee@gmail.com";
    
    return sendEmail($inquiryData['email'], $inquiryData['name'], $subject, $htmlBody, $plainBody);
}
?>
