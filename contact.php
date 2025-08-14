<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Contact form script started");

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log incoming data for debugging
    error_log('Received POST request: ' . file_get_contents('php://input'));
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Debug log
    error_log('Decoded data: ' . print_r($data, true));
    
    // Validate required fields
    $required = ['firstName', 'lastName', 'email', 'message'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            exit;
        }
    }

    // Configure email settings
    $smtp_host = "smtp.hostinger.com";
    $smtp_port = 465;
    $smtp_user = "contact@infinitejobssolutions.com";
    $smtp_pass = "your-email-password";
    $company_email = "contact@infinitejobssolutions.com";

    // Prepare email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $smtp_user\r\n";

    // Company email content
    $company_subject = "New Contact Form Submission - " . ($data['service'] ?? 'General Inquiry');
    $company_message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #00D4FF;'>New Contact Form Submission</h2>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <p><strong>Name:</strong> {$data['firstName']} {$data['lastName']}</p>
                <p><strong>Email:</strong> {$data['email']}</p>
                " . (!empty($data['phone']) ? "<p><strong>Phone:</strong> {$data['phone']}</p>" : "") . "
                " . (!empty($data['service']) ? "<p><strong>Service Interest:</strong> {$data['service']}</p>" : "") . "
                <p><strong>Message:</strong></p>
                <div style='background: white; padding: 15px; border-radius: 5px; margin-top: 10px;'>
                    " . nl2br(htmlspecialchars($data['message'])) . "
                </div>
            </div>
        </div>";

    // User confirmation email content
    $user_subject = "Thank you for contacting Infinite Jobs Solutions";
    $user_message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #00D4FF;'>Thank You for Your Message!</h2>
            <p>Dear {$data['firstName']},</p>
            <p>Thank you for reaching out to Infinite Jobs Solutions. We have received your message and will get back to you within 24 hours.</p>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3>Your Message Details:</h3>
                <p><strong>Service Interest:</strong> " . ($data['service'] ?? 'General Inquiry') . "</p>
                <p><strong>Your Message:</strong></p>
                <div style='background: white; padding: 15px; border-radius: 5px; margin-top: 10px;'>
                    " . nl2br(htmlspecialchars($data['message'])) . "
                </div>
            </div>
            <p>In the meantime, feel free to explore our services or call us directly at <strong>+1 (555) 123-4567</strong>.</p>
        </div>";

    // Send emails
    $company_mail_sent = mail($company_email, $company_subject, $company_message, $headers);
    $user_mail_sent = mail($data['email'], $user_subject, $user_message, $headers);

    if ($company_mail_sent && $user_mail_sent) {
        echo json_encode(['success' => true, 'message' => 'Contact form submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send email']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>