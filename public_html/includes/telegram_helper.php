<?php
// Helper: call Telegram API methods with error logging
// Safe: объявляем функции только если они ещё не определены.

// IDE-stub: константы уже объявлены в config.php через .env, но здесь для подсказок IDE
if (!defined('TELEGRAM_BOT_TOKEN')) {
    define('TELEGRAM_BOT_TOKEN', '');
}
if (!defined('TELEGRAM_CHAT_ID')) {
    define('TELEGRAM_CHAT_ID', '');
}

if (!function_exists('callTelegramMethod')) {
    function callTelegramMethod(string $method, array $params = [], $chatIdDefault = TELEGRAM_CHAT_ID): mixed
    {
        $token = TELEGRAM_BOT_TOKEN;
        $url = "https://krasa-studio.roccreate.workers.dev/bot{$token}/{$method}";

        if (!isset($params['chat_id']) && $chatIdDefault) {
            $params['chat_id'] = $chatIdDefault;
        }
        if (!isset($params['parse_mode'])) {
            $params['parse_mode'] = 'HTML';
        }

        $logFile = __DIR__ . '/../../logs/telegram_errors.log';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false || $curlError) {
            $entry = date('Y-m-d H:i:s') . " | cURL request failed\n";
            $entry .= "Method: $method\nURL: $url\nPayload: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";
            $entry .= "cURL error: $curlError\n\n";
            @file_put_contents($logFile, $entry, FILE_APPEND);
            return false;
        }

        $resp = json_decode($result, true);
        if (!isset($resp['ok']) || $resp['ok'] !== true) {
            $entry = date('Y-m-d H:i:s') . " | Telegram API returned error\n";
            $entry .= "Method: $method\nURL: $url\nPayload: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";
            $entry .= "Response: " . $result . "\n\n";
            @file_put_contents($logFile, $entry, FILE_APPEND);
        }

        return $resp;
    }
}

if (!function_exists('sendToTelegram')) {
    // Backward compatible wrapper: принимает либо строку, либо массив параметров
    function sendToTelegram(string|array $message, $chatId = TELEGRAM_CHAT_ID): mixed
    {
        if (is_array($message)) {
            $data = $message;
        } else {
            $data = [
                'chat_id'    => $chatId,
                'text'       => $message,
                'parse_mode' => 'HTML'
            ];
        }
        return callTelegramMethod('sendMessage', $data, $chatId);
    }
}
