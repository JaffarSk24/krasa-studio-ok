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

    // Проверка обязательных полей
    $requiredFields = ['date', 'time', 'phone', 'name', 'service_id'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field {$field} is required OR not passed");
        }
    }

    // Проверка reCAPTCHA
    if (!isset($_POST['recaptcha_token']) || !verifyRecaptcha($_POST['recaptcha_token'])) {
        throw new Exception('reCAPTCHA verification failed');
    }

    $date       = $_POST['date'];
    $time       = $_POST['time'];
    $phone      = $_POST['phone'];
    $name       = trim($_POST['name']);
    $serviceId  = $_POST['service_id'];
    $message    = $_POST['message'] ?? null;

    $slotKey = "$date $time";

    // Проверка блокировки
    $blockedFile = __DIR__ . '/../data/blocked_slots.txt';
    $blocked = file_exists($blockedFile) ? file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    if (in_array($slotKey, $blocked)) {
        throw new Exception("Slot already blocked: $slotKey");
    }

    // Запись в БД
    $db = new Database();
    $conn = $db->getConnection();
    $bookingId = generateUUID();

    $stmt = $conn->prepare("
        INSERT INTO bookings (id, booking_date, booking_time, service_id, client_name, client_phone, message, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $dateObj = DateTime::createFromFormat('d-m-Y', $date);
    $dateIso = $dateObj ? $dateObj->format('Y-m-d') : $date;

    $stmt->execute([$bookingId, $dateIso, $time, $serviceId, $name, $phone, $message]);

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

    // Формируем сообщение в Telegram
    $dateObj = DateTime::createFromFormat('d-m-Y', $date);
    $dateForTelegram = $dateObj ? $dateObj->format('d-m-Y') : $date;

    $telegramMessage  = "🔔 " . t('new_booking') . "\n\n";
    $telegramMessage .= "📂 " . t('category') . ": $categoryName\n";
    $telegramMessage .= "📋 " . t('service') . ": $serviceName\n\n";
    $telegramMessage .= "📅 " . t('date') . ": $dateForTelegram\n";
    $telegramMessage .= "⏰ " . t('time') . ": $time\n\n";
    $telegramMessage .= "👤 " . t('name') . ": $name\n";
    $telegramMessage .= "📞 " . t('phone_number') . ": $phone\n";
    if ($message) {
        $telegramMessage .= "💬 " . t('message') . ": $message\n";
    }

    try {
        $keyboard = [
            'inline_keyboard' => [[
                ['text' => t('approve_tg_button'), 'callback_data' => "approve|$bookingId"]
            ]]
        ];
        $params = [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $telegramMessage,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard)
        ];

        require_once __DIR__ . '/../includes/telegram_helper.php';
        sendToTelegram($params);
    } catch (Exception $te) {
        // Можно логировать ошибку отправки в Telegram, если нужно
    }

    echo json_encode([
        'success' => true,
        'message' => t('booking_success'),
        'booking_id' => $bookingId
    ]);

} catch (Exception $e) {
    $logFile = __DIR__ . '/../logs/booking_errors.log';
    $entry = date('Y-m-d H:i:s') . " | Exception: " . $e->getMessage() . "\n";
    $entry .= "Trace: " . $e->getTraceAsString() . "\n";
    $entry .= "POST: " . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "\n\n";
    @file_put_contents($logFile, $entry, FILE_APPEND);

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
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