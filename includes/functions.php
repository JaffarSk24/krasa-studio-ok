<?php
require_once 'config.php';
require_once 'database.php';
require_once __DIR__ . '/../lang/translations.php';

// Функция для отправки сообщений в Telegram
function sendToTelegram($message, $chatId = TELEGRAM_CHAT_ID) {
    $telegramToken = TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot{$telegramToken}/sendMessage";

    // Если пришёл массив → значит, это уже готовые параметры (с клавиатурой и т.д.)
    if (is_array($message)) {
        $data = $message;
        if (!isset($data['chat_id'])) {
            $data['chat_id'] = $chatId;
        }
        if (!isset($data['parse_mode'])) {
            $data['parse_mode'] = 'HTML';
        }
    } else {
        // Иначе обычное сообщение (как раньше)
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
    }

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded
",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

// Функция для проверки reCAPTCHA
function verifyRecaptcha($recaptchaResponse) {
    $secretKey = RECAPTCHA_SECRET_KEY;
    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded
",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $resultJson = json_decode($result, true);

    return $resultJson['success'] === true && $resultJson['score'] >= 0.5;
}

// Функция для генерации временных слотов
function generateTimeSlots($startDate, $endDate) {
    $db = new Database();
    $conn = $db->getConnection();

    $currentDate = new DateTime($startDate);
    $endDate = new DateTime($endDate);

    while ($currentDate <= $endDate) {
        if ($currentDate->format('N') <= 5) { // будни
            for ($hour = 9; $hour <= 20; $hour++) {
                $time = sprintf('%02d:00', $hour);

                $stmt = $conn->prepare("
                    INSERT IGNORE INTO time_slots (date, time) 
                    VALUES (:date, :time)
                ");
                $stmt->execute([
                    ':date' => $currentDate->format('Y-m-d'),
                    ':time' => $time
                ]);
            }
        }
        $currentDate->add(new DateInterval('P1D'));
    }
}

// Функция для получения доступных слотов
function getAvailableTimeSlots($serviceId = null) {
    $db = new Database();
    $conn = $db->getConnection();

    $sql = "
        SELECT ts.id, ts.date, ts.time 
        FROM time_slots ts 
        LEFT JOIN bookings b ON ts.id = b.time_slot_id AND b.status != 'cancelled'
        WHERE ts.is_active = 1 
        AND ts.date >= CURDATE() 
        AND b.id IS NULL 
        ORDER BY ts.date, ts.time
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Функция для безопасного вывода данных
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Функция для редиректа
function redirect($url) {
    header("Location: $url");
    exit();
}

// Функция для получения текущего URL
function getCurrentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
           "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
}

// Функция для генерации URL с языком
function url($path, $lang = null) {
    if (!$lang) $lang = CURRENT_LANG;
    $baseUrl = rtrim(SITE_URL, '/');
    return "{$baseUrl}/{$path}?lang={$lang}";
}

// Функция для форматирования цены
function formatPrice($price, $currency = '€') {
    return number_format($price, 2, ',', ' ') . ' ' . $currency;
}

// Функция для форматирования времени
function formatTime($minutes) {
    $hours = intval($minutes / 60);
    $mins = $minutes % 60;

    if ($hours > 0 && $mins > 0) {
        return "{$hours}h {$mins}min";
    } elseif ($hours > 0) {
        return "{$hours}h";
    } else {
        return "{$mins}min";
    }
}

// Функция для получения мета-тегов страницы
function getPageMeta($page, $data = []) {
    $lang = CURRENT_LANG;
    $siteName = SITE_NAME;

    $meta = [
        'title' => $siteName,
        'description' => t('meta_description'),
        'keywords' => t('meta_keywords'),
        'og_image' => SITE_URL . '/assets/images/logo-og.jpg'
    ];

    switch ($page) {
        case 'about':
            $meta['title'] = t('about') . ' - ' . $siteName;
            break;
        case 'services':
            $meta['title'] = t('services') . ' - ' . $siteName;
            break;
        case 'pricing':
            $meta['title'] = t('pricing') . ' - ' . $siteName;
            break;
        case 'gallery':
            $meta['title'] = t('gallery') . ' - ' . $siteName;
            break;
        case 'blog':
            $meta['title'] = t('blog') . ' - ' . $siteName;
            break;
        case 'contacts':
            $meta['title'] = t('contacts') . ' - ' . $siteName;
            break;
    }

    return $meta;
}
?>