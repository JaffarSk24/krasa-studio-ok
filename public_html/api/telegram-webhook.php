<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

// --- Вспомогательные функции для массовой блокировки/разблокировки ---
require_once '../includes/telegram_helper.php';

function getSlotsForDay($dateIso)
{
    // Генерируем с 09:00 до 20:00 (те, что доступны для записи)
    $slots = [];
    for ($h = 9; $h <= 20; $h++) {
        $slots[] = sprintf('%s %02d:00', $dateIso, $h);
        if ($h !== 20) {
            $slots[] = sprintf('%s %02d:30', $dateIso, $h);
        }
    }
    return $slots;
}

function modifyBlockedSlots($slotsToModify, $action)
{
    $blockedFile = __DIR__ . '/../data/blocked_slots.txt';
    $blocked = [];
    if (file_exists($blockedFile)) {
        $lines = file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $l) {
            $l = trim($l);
            if ($l) $blocked[] = $l;
        }
    }

    if ($action === 'block') {
        foreach ($slotsToModify as $slot) {
            if (!in_array($slot, $blocked)) {
                $blocked[] = $slot;
            }
        }
    } else if ($action === 'unblock') {
        $blocked = array_filter($blocked, function ($slot) use ($slotsToModify) {
            return !in_array($slot, $slotsToModify);
        });
    }

    $blocked = array_values(array_unique($blocked));
    sort($blocked);
    file_put_contents($blockedFile, implode("\n", $blocked) . "\n");
}

function getDatesKeyboard($actionStr, $page, $prefix, $extraData = '')
{
    $daysPerPage = 6;
    $today = new DateTime();
    $today->modify('+' . ($page * $daysPerPage) . ' days');

    $keyboard = [];
    $row = [];
    for ($i = 0; $i < $daysPerPage; $i++) {
        $dateObj = clone $today;
        $dateObj->modify("+$i days");
        $dateIso = $dateObj->format('Y-m-d');
        $dateStr = $dateObj->format('d.m.Y');

        $weekdays = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        $dw = $weekdays[$dateObj->format('w')];

        $text = "$dateStr ($dw)";
        $cbData = "$prefix|$actionStr|$dateIso" . ($extraData ? "|$extraData" : "");

        $row[] = ['text' => $text, 'callback_data' => substr($cbData, 0, 64)];
        if (count($row) == 2) {
            $keyboard[] = $row;
            $row = [];
        }
    }
    if (count($row) > 0) $keyboard[] = $row;

    $navRow = [];
    if ($page > 0) {
        $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => "act_nav|$actionStr|$prefix|" . ($page - 1) . ($extraData ? "|$extraData" : "")];
    }
    if ($page < 5) {
        $navRow[] = ['text' => 'Вперед ➡️', 'callback_data' => "act_nav|$actionStr|$prefix|" . ($page + 1) . ($extraData ? "|$extraData" : "")];
    }
    if (count($navRow) > 0) $keyboard[] = $navRow;

    return $keyboard;
}

function getTimesKeyboard($actionStr, $page, $dateIso, $prefix, $extraData = '')
{
    $slots = [];
    for ($h = 9; $h <= 20; $h++) {
        $slots[] = sprintf('%02d:00', $h);
        if ($h !== 20) {
            $slots[] = sprintf('%02d:30', $h);
        }
    }

    $perPage = 6;
    $totalPages = ceil(count($slots) / $perPage);
    $offset = $page * $perPage;
    $pageSlots = array_slice($slots, $offset, $perPage);

    $keyboard = [];
    $row = [];
    foreach ($pageSlots as $time) {
        $cbData = "$prefix|$actionStr|$dateIso|$time" . ($extraData ? "|$extraData" : "");
        $row[] = ['text' => $time, 'callback_data' => substr($cbData, 0, 64)];
        if (count($row) == 2) {
            $keyboard[] = $row;
            $row = [];
        }
    }
    if (count($row) > 0) $keyboard[] = $row;

    $navRow = [];
    if ($page > 0) {
        $navRow[] = ['text' => '⬅️', 'callback_data' => "act_nav|$actionStr|$prefix|" . ($page - 1) . "|$dateIso" . ($extraData ? "|$extraData" : "")];
    }
    if ($page < $totalPages - 1) {
        $navRow[] = ['text' => '➡️', 'callback_data' => "act_nav|$actionStr|$prefix|" . ($page + 1) . "|$dateIso" . ($extraData ? "|$extraData" : "")];
    }
    if (count($navRow) > 0) $keyboard[] = $navRow;

    return $keyboard;
}

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
        }
    } elseif ($action === 'act_nav') {
        $sign = $parts[1];
        $prefix = $parts[2];
        $page = intval($parts[3]);

        if ($prefix === 'act_start' || $prefix === 'act_end') {
            $extraData = $parts[4] ?? '';
            $keyboard = getDatesKeyboard($sign, $page, $prefix, $extraData);

            $text = ($sign === '-') ? "С какой даты вы хотите начать блокировку слотов?" : "С какой даты вы хотите начать разблокировку слотов?";
            if ($prefix === 'act_end') {
                $text = ($sign === '-') ? "До какой даты вы хотите установить блокировку слотов?" : "До какой даты вы хотите установить разблокировку слотов?";
                array_unshift($keyboard, [['text' => 'Только один этот день', 'callback_data' => "act_end|$sign|$extraData|same_day"]]);
            }

            callTelegramMethod('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
            ]);
            exit;
        } elseif ($prefix === 'act_tstart' || $prefix === 'act_tend') {
            $dateIso = $parts[4];
            $extraData = $parts[5] ?? '';
            $keyboard = getTimesKeyboard($sign, $page, $dateIso, $prefix, $extraData);

            $text = ($sign === '-') ? "С какого времени начать блокировку ($dateIso)?" : "С какого времени начать разблокировку ($dateIso)?";
            if ($prefix === 'act_tend') {
                $text = ($sign === '-') ? "Выберите время окончания ($dateIso):" : "Выберите время окончания разблокировки ($dateIso):";
            }

            callTelegramMethod('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
            ]);
            exit;
        }
    } elseif ($action === 'act_start') {
        $sign = $parts[1];
        $dateStart = $parts[2];

        $keyboard = getDatesKeyboard($sign, 0, 'act_end', $dateStart);
        array_unshift($keyboard, [['text' => 'Только один этот день', 'callback_data' => "act_end|$sign|$dateStart|same_day"]]);

        $text = ($sign === '-') ? "До какой даты вы хотите установить блокировку слотов?" : "До какой даты вы хотите установить разблокировку слотов?";

        callTelegramMethod('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
        exit;
    } elseif ($action === 'act_end') {
        $sign = $parts[1];
        $dateStart = $parts[2];
        $dateEnd = $parts[3];

        if ($dateEnd === 'same_day') {
            $keyboard = getTimesKeyboard($sign, 0, $dateStart, 'act_tstart');
            $text = ($sign === '-') ? "С какого времени начать блокировку ($dateStart)?" : "С какого времени начать разблокировку ($dateStart)?";
            callTelegramMethod('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
            ]);
            exit;
        } else {
            $startObj = new DateTime($dateStart);
            $endObj = new DateTime($dateEnd);

            if ($startObj > $endObj) {
                $temp = $startObj;
                $startObj = $endObj;
                $endObj = $temp;
            }

            $slotsToModify = [];
            for ($d = clone $startObj; $d <= $endObj; $d->modify('+1 day')) {
                $slotsToModify = array_merge($slotsToModify, getSlotsForDay($d->format('Y-m-d')));
            }

            $actionType = ($sign === '-') ? 'block' : 'unblock';
            modifyBlockedSlots($slotsToModify, $actionType);

            $word = ($sign === '-') ? 'Блокировка' : 'Разблокировка';
            $text = "✅ $word слотов с {$startObj->format('d.m.Y')} по {$endObj->format('d.m.Y')} выполнена успешно.";

            callTelegramMethod('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text
            ]);
            exit;
        }
    } elseif ($action === 'act_tstart') {
        $sign = $parts[1];
        $date = $parts[2];
        $timeStart = $parts[3];

        $keyboard = getTimesKeyboard($sign, 0, $date, 'act_tend', $timeStart);
        $text = ($sign === '-') ? "Выберите время окончания блокировки ($date):" : "Выберите время окончания разблокировки ($date):";

        callTelegramMethod('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
        exit;
    } elseif ($action === 'act_tend') {
        $sign = $parts[1];
        $date = $parts[2];
        $timeEnd = $parts[3];
        $timeStart = $parts[4] ?? '';

        if (strcmp($timeStart, $timeEnd) > 0) {
            $temp = $timeStart;
            $timeStart = $timeEnd;
            $timeEnd = $temp;
        }

        $allSlots = getSlotsForDay($date);
        $slotsToModify = [];
        foreach ($allSlots as $slot) {
            $t = explode(' ', $slot)[1];
            if (strcmp($t, $timeStart) >= 0 && strcmp($t, $timeEnd) <= 0) {
                $slotsToModify[] = $slot;
            }
        }

        $actionType = ($sign === '-') ? 'block' : 'unblock';
        modifyBlockedSlots($slotsToModify, $actionType);

        $word = ($sign === '-') ? 'Блокировка' : 'Разблокировка';
        $text = "✅ $word слотов за $date с $timeStart по $timeEnd выполнена успешно.";

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

// --- 2) Обработка команд бота для блокировки/разблокировки слотов ---
if (isset($update['message']) && isset($update['message']['text'])) {
    $chatId = $update['message']['chat']['id'];
    $textMsg = trim($update['message']['text']);

    if ($textMsg === '-' || $textMsg === '+') {
        $sign = $textMsg;
        $keyboard = getDatesKeyboard($sign, 0, 'act_start');
        $text = ($sign === '-') ? "С какой даты вы хотите начать блокировку слотов?" : "С какой даты вы хотите начать разблокировку слотов?";

        callTelegramMethod('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
        ]);
        exit;
    }

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

        $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
        $params = [
            'chat_id' => $chatId,
            'text' => $reply
        ];
        file_get_contents($url . "?" . http_build_query($params));
    }
}
