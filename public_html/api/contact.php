
<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate required fields
    $requiredFields = ['name', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field {$field} is required");
        }
    }
    
    // Verify reCAPTCHA
    if (!isset($_POST['recaptcha_token']) || !verifyRecaptcha($_POST['recaptcha_token'])) {
        throw new Exception('reCAPTCHA verification failed');
    }
    
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message']);
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Save contact to database
    $contactId = generateUUID();
    $stmt = $conn->prepare("
        INSERT INTO contacts (id, name, phone, email, message, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'new', NOW())
    ");
    $stmt->execute([$contactId, $name, $phone ?: null, $email ?: null, $message]);
    
    // Send notification to Telegram
    $telegramMessage = "📨 Nová správa z kontaktného formulára!\n\n";
    $telegramMessage .= "👤 Meno: {$name}\n";
    if ($phone) {
        $telegramMessage .= "📞 Telefón: {$phone}\n";
    }
    if ($email) {
        $telegramMessage .= "📧 Email: {$email}\n";
    }
    $telegramMessage .= "💬 Správa:\n{$message}\n";
    $telegramMessage .= "\n#kontakt #krásaštúdioOK";
    
    try {
        sendToTelegram($telegramMessage);
    } catch (Exception $e) {
        // Log telegram error but don't fail the contact form
        error_log("Telegram notification failed: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => t('contact_success'),
        'contact_id' => $contactId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
?>
