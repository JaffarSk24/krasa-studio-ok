<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

// --- 1) Обработка callback-кнопок ---
if (isset($update['callback_query'])) {
    $callback = $update['callback_query'];
    $data = $callback['data']; // "approve|{booking_id}" или "approve|YYYY-MM-DD|HH:MM"
    $chatId = $callback['message']['chat']['id'];
    $messageId = $callback['message']['message_id'];

    $parts = explode('|', $data);
    $action = $parts[0] ?? '';

    if ($action === 'approve') {
        if (count($parts) === 2) {
            // НОВЫЙ формат: approve|booking_id
            $bookingId = $parts[1];

            $db = new Database();
            $conn = $db->getConnection();

            // Получаем бронь по ID
            $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? LIMIT 1");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                require_once __DIR__ . '/../includes/telegram_helper.php';
                callTelegramMethod('editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "Бронь не найдена (ID: $bookingId)"
                ]);
                exit;
            }

            if (($booking['status'] ?? '') !== 'pending') {
                require_once __DIR__ . '/../includes/telegram_helper.php';
                callTelegramMethod('editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "Статус брони: " . ($booking['status'] ?? '-')
                ]);
                exit;
            }

            // Обновляем статус на 'confirmed'
            $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$bookingId]);

            // Получаем данные брони с услугой
            $stmt = $conn->prepare("
                SELECT b.client_name, b.client_phone, b.message, b.booking_date, b.booking_time, s.name_sk, s.name_ru, s.name_ua
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                WHERE b.id = ?
                LIMIT 1
            ");
            $stmt->execute([$bookingId]);
            $full = $stmt->fetch(PDO::FETCH_ASSOC);

            $clientName  = $full['client_name'] ?? '-';
            $clientPhone = $full['client_phone'] ?? '-';
            $serviceName = getLocalizedField($full, 'name');
            $bookingMessage = $full['message'] ?? '';
            $dateIso = $full['booking_date'] ?? null; // ISO формат из базы
            $time = $full['booking_time'] ?? '';

            // Записываем слот в blocked_slots.txt (ISO формат)
            $blockedFile = __DIR__ . '/../data/blocked_slots.txt';
            if ($dateIso && $time) {
                $slotKey = trim($dateIso . ' ' . $time);
                file_put_contents($blockedFile, substr($slotKey, 0, 16) . "\n", FILE_APPEND);
            }

            // Для отображения в Telegram преобразуем ISO в d-m-Y
            $dateObj = DateTime::createFromFormat('Y-m-d', $dateIso);
            $dateFormatted = $dateObj ? $dateObj->format('d-m-Y') : $dateIso;

            // Формируем сообщение для Telegram
            $text  = "✅ Бронь подтверждена\n";
            $text .= "📅 " . $dateFormatted . " ⏰ " . substr(($time ?: '-'), 0, 5) . "\n";
            $text .= "👤 $clientName 📞 $clientPhone\n";
            $text .= "📋 $serviceName";
            if ($bookingMessage) {
                $text .= " 💬 " . $bookingMessage;
            }

            require_once __DIR__ . '/../includes/telegram_helper.php';
            callTelegramMethod('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text
            ]);
            exit;

        } elseif (count($parts) === 3) {
            // СТАРЫЙ формат: approve|YYYY-MM-DD|HH:MM
            $dateIso = $parts[1];
            $time = $parts[2];
            $slotKey = "$dateIso $time";

            $db = new Database();
            $conn = $db->getConnection();

            // Проверяем бронь
            $stmt = $conn->prepare("SELECT id, status FROM bookings WHERE booking_date = ? AND booking_time = ? LIMIT 1");
            $stmt->execute([$dateIso, $time]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$exists) {
                require_once __DIR__ . '/../includes/telegram_helper.php';
                callTelegramMethod('editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "Бронь не найдена для $slotKey"
                ]);
                exit;
            }

            if ($exists['status'] !== 'pending') {
                require_once __DIR__ . '/../includes/telegram_helper.php';
                callTelegramMethod('editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "Статус брони: " . $exists['status']
                ]);
                exit;
            }

            // Подтверждаем бронь
            $stmt = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id = ?");
            $stmt->execute([$exists['id']]);

            // Получаем данные брони
            $stmt = $conn->prepare("
                SELECT b.client_name, b.client_phone, b.message, b.booking_date, b.booking_time, s.name_sk, s.name_ru, s.name_ua
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                WHERE b.id = ?
                LIMIT 1
            ");
            $stmt->execute([$exists['id']]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            $clientName  = $booking['client_name'] ?? '-';
            $clientPhone = $booking['client_phone'] ?? '-';
            $serviceName = getLocalizedField($booking, 'name');
            $bookingMessage = $booking['message'] ?? '';

            // Записываем слот в blocked_slots.txt
            $blockedFile = __DIR__ . '/../data/blocked_slots.txt';
            file_put_contents($blockedFile, substr($slotKey, 0, 16) . "\n", FILE_APPEND);

            // Для отображения в Telegram преобразуем ISO в d-m-Y
            $dateObj = DateTime::createFromFormat('Y-m-d', $dateIso);
            $dateFormatted = $dateObj ? $dateObj->format('d-m-Y') : $dateIso;

            // Формируем сообщение
            $text  = "✅ Бронь подтверждена\n";
            $text .= "📅 $dateFormatted ⏰ $time\n";
            $text .= "👤 $clientName 📞 $clientPhone\n";
            $text .= "📋 $serviceName";
            if ($bookingMessage) {
                $text .= " 💬 " . $bookingMessage;
            }

            require_once __DIR__ . '/../includes/telegram_helper.php';
            callTelegramMethod('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text
            ]);
            exit;
        } else {
            // Неизвестный формат callback_data
            require_once __DIR__ . '/../includes/telegram_helper.php';
            callTelegramMethod('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "Неверный формат callback: $data"
            ]);
            exit;
        }
    }
}

// --- 2) Обработка команд бота для блокировки/разблокировки слотов ---
if (isset($update['message']) && isset($update['message']['text'])) {
    $chatId = $update['message']['chat']['id'];
    $textMsg = trim($update['message']['text']);

    // Формат команды: +/- d-m-Y HH:MM
    if (preg_match('/^([-+])\s*(\d{2}-\d{2}-\d{4})\s+(\d{2}:\d{2})$/', $textMsg, $m)) {
        $sign = $m[1];
        $dateInput = $m[2];
        $time = $m[3];

        // Преобразуем дату в ISO для записи
        $dateObj = DateTime::createFromFormat('d-m-Y', $dateInput);
        $dateIso = $dateObj ? $dateObj->format('Y-m-d') : null;

        if (!$dateIso) {
            // Некорректная дата — можно отправить сообщение об ошибке или игнорировать
            exit;
        }

        $slotKey = "$dateIso $time";
        $blockedFile = __DIR__ . '/../data/blocked_slots.txt';

        if ($sign === '-') {
            // Блокируем слот
            file_put_contents($blockedFile, $slotKey . "\n", FILE_APPEND);
            $reply = "⛔ Слот заблокирован вручную:\n📅 $dateInput ⏰ $time";
        } elseif ($sign === '+') {
            // Разблокируем слот
            if (file_exists($blockedFile)) {
                $lines = file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $lines = array_filter($lines, fn($l) => trim($l) !== trim($slotKey));
                file_put_contents($blockedFile, implode("\n", $lines) . "\n");
                $reply = "✅ Слот разблокирован:\n📅 $dateInput ⏰ $time";
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