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
    $fieldLabels = [
        'name' => t('name'),
        'message' => t('message'),
    ];

    $requiredFields = ['name', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $label = $fieldLabels[$field] ?? $field;
            throw new Exception(sprintf(t('field_required'), $label));
        }
    }

    if (!isset($_POST['recaptcha_token']) || !verifyRecaptcha($_POST['recaptcha_token'])) {
        throw new Exception(t('recaptcha_failed'));
    }

    $name = trim($_POST['name']);
    try {
        $phone = isset($_POST['phone']) && $_POST['phone'] !== ''
            ? normalizePhoneNumber($_POST['phone'])
            : '';
    } catch (InvalidArgumentException $e) {
        throw new Exception(t('invalid_phone_number'));
    }
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message']);

    $db = new Database();
    $conn = $db->getConnection();

    $contactId = generateUUID();
    $stmt = $conn->prepare("
        INSERT INTO contacts (id, name, phone, email, message, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'new', NOW())
    ");
    $stmt->execute([$contactId, $name, $phone ?: null, $email ?: null, $message]);

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