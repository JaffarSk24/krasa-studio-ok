<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

// --- 1) Обработка callback-кнопок ---
if (isset($update['callback_query'])) {
    $callback = $update['callback_query'];
    $data = $callback['data']; // "approve|2025-09-30|09:00"
    $chatId = $callback['message']['chat']['id'];
    $messageId = $callback['message']['message_id'];

    list($action, $date, $time) = explode('|', $data);

    if ($action === 'approve') {
        $slotKey = "$date $time";

        $db = new Database(); 
        $conn = $db->getConnection();

        // 1) Обновляем бронь
        $stmt = $conn->prepare("UPDATE bookings SET status='approved' WHERE booking_date=? AND booking_time=?");
        $stmt->execute([$date, $time]);

        // 2) Получаем данные брони вместе с услугой
        $stmt = $conn->prepare("
            SELECT b.client_name, b.client_phone, s.name_sk, s.name_ru, s.name_ua
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            WHERE b.booking_date=? AND b.booking_time=?
            LIMIT 1
        ");
        $stmt->execute([$date, $time]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        $clientName  = $booking['client_name'] ?? '-';
        $clientPhone = $booking['client_phone'] ?? '-';
        $serviceName = getLocalizedField($booking, 'name');

        // 3) Блокируем слот
        $blockedFile = __DIR__ . '/../data/blocked_slots.txt';
        file_put_contents($blockedFile, $slotKey . "\n", FILE_APPEND);

        // 4) Ответ в Telegram — редактируем исходное сообщение
        $text  = "✅ Бронь подтверждена\n";
        $text .= "📅 $date ⏰ $time\n";
        $text .= "👤 $clientName 📞 $clientPhone\n";
        $text .= "📋 $serviceName 💬 $message";

        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text
        ];
        file_get_contents("https://api.telegram.org/bot".TELEGRAM_BOT_TOKEN."/editMessageText?".http_build_query($params));
    }
    exit;
}

// --- 2) Обработка обычных сообщений ---
if (isset($update['message']) && isset($update['message']['text'])) {
    $chatId = $update['message']['chat']['id'];
    $textMsg = trim($update['message']['text']);

    // Проверим формат: +/- YYYY-MM-DD HH:MM
    if (preg_match('/^([-+])\s*(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})$/', $textMsg, $m)) {
        $sign = $m[1];
        $date = $m[2];
        $time = $m[3];
        $slotKey = "$date $time";
        $blockedFile = __DIR__ . '/../data/blocked_slots.txt';

        if ($sign === '-') {
            file_put_contents($blockedFile, $slotKey . "\n", FILE_APPEND);
            $reply = "⛔ Слот заблокирован вручную:\n📅 $date ⏰ $time";
        } elseif ($sign === '+') {
            if (file_exists($blockedFile)) {
                $lines = file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $lines = array_filter($lines, fn($l) => trim($l) !== $slotKey);
                file_put_contents($blockedFile, implode("\n", $lines) . "\n");
                $reply = "✅ Слот разблокирован:\n📅 $date ⏰ $time";
            } else {
                $reply = "ℹ️ Файл блокировок не найден, слот не разблокирован.";
            }
        }

        $url = "https://api.telegram.org/bot".TELEGRAM_BOT_TOKEN."/sendMessage";
        $params = [
            'chat_id' => $chatId,
            'text' => $reply
        ];
        file_get_contents($url."?".http_build_query($params));
    }
}