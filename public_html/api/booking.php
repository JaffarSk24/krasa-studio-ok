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

$debugSteps = [];

try {
    $debugSteps[] = "STEP 1: booking.php started";

    // Проверка обязательных полей
    $requiredFields = ['date', 'time', 'phone', 'name', 'service_id'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field {$field} is required OR not passed");
        }
    }
    $debugSteps[] = "STEP 2: Required fields OK";

    // Проверка reCAPTCHA
    if (!isset($_POST['recaptcha_token']) || !verifyRecaptcha($_POST['recaptcha_token'])) {
        throw new Exception('reCAPTCHA verification failed');
    }
    $debugSteps[] = "STEP 3: reCAPTCHA passed";

    $date       = $_POST['date'];
    $time       = $_POST['time'];
    $phone      = $_POST['phone'];
    $name       = trim($_POST['name']);
    $serviceId  = $_POST['service_id'];
    $message    = $_POST['message'] ?? null;

    $slotKey = "$date $time";
    $debugSteps[] = "STEP 4: slotKey=$slotKey | service_id=$serviceId";

    // Проверка блокировки
    $blockedFile = __DIR__ . '/../data/blocked_slots.txt';
    $blocked = file_exists($blockedFile) ? file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    if (in_array($slotKey, $blocked)) {
        throw new Exception("Slot already blocked: $slotKey");
    }
    $debugSteps[] = "STEP 5: Slot is free";

    // Запись в БД
    $db = new Database();
    $conn = $db->getConnection();
    $bookingId = generateUUID();

    $stmt = $conn->prepare("
        INSERT INTO bookings (id, booking_date, booking_time, service_id, client_name, client_phone, message, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$bookingId, $date, $time, $serviceId, $name, $phone, $message]);
    $debugSteps[] = "STEP 6: Booking inserted with ID=$bookingId";

    // Получаем данные услуги и категории
    $stmt = $conn->prepare("
        SELECT s.*, 
               sc.name_sk as category_sk, sc.name_ru as category_ru, sc.name_ua as category_ua
        FROM services s
        JOIN service_categories sc ON s.category_id = sc.id
        WHERE s.id = ?
        LIMIT 1
    ");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        throw new Exception("Service not found for ID=$serviceId");
    }

    $serviceName  = getLocalizedField($service, 'name');
    $categoryName = getLocalizedField($service, 'category');

    $debugSteps[] = "STEP 7: Loaded service=$serviceName | category=$categoryName";

    // Формируем сообщение в Telegram
    $telegramMessage  = "🔔 " . t('new_booking') . "\n\n";
    $telegramMessage .= "📂 " . t('category') . ": $categoryName\n";
    $telegramMessage .= "📋 " . t('service') . ": $serviceName\n\n";
    $telegramMessage .= "📅 " . t('date') . ": $date\n";
    $telegramMessage .= "⏰ " . t('time') . ": $time\n\n";
    $telegramMessage .= "👤 " . t('name') . ": $name\n";
    $telegramMessage .= "📞 " . t('phone_number') . ": $phone\n";
    if ($message) {
        $telegramMessage .= "💬 " . t('message') . ": $message\n";
    }

    try {
        $keyboard = [
            'inline_keyboard' => [[
                ['text' => t('approve_tg_button'), 'callback_data' => "approve|$date|$time"]
            ]]
        ];
        $params = [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $telegramMessage,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard)
        ];
        sendToTelegram($params);
        $debugSteps[] = "STEP 8: Sent to Telegram";
    } catch (Exception $te) {
        $debugSteps[] = "STEP 8: Telegram failed - " . $te->getMessage();
    }

    echo json_encode([
        'success' => true,
        'message' => t('booking_success'),
        'booking_id' => $bookingId,
        'debug' => $debugSteps
    ]);

} catch (Exception $e) {
    http_response_code(400);
    $debugSteps[] = "ERROR: " . $e->getMessage();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $debugSteps
    ]);
}

// --- Вспомогательная функция генерации UUID ---
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