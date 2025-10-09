<?php
// Helper: call Telegram API methods with error logging
// Safe: объявляем функции только если они ещё не определены.

if (!function_exists('callTelegramMethod')) {
    function callTelegramMethod($method, $params = [], $chatIdDefault = TELEGRAM_CHAT_ID) {
        $token = TELEGRAM_BOT_TOKEN;
        $url = "https://api.telegram.org/bot{$token}/{$method}";

        if (!isset($params['chat_id']) && $chatIdDefault) {
            $params['chat_id'] = $chatIdDefault;
        }
        if (!isset($params['parse_mode'])) {
            $params['parse_mode'] = 'HTML';
        }

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($params),
                'timeout' => 10
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        $logFile = __DIR__ . '/../../logs/telegram_errors.log';

        if ($result === false) {
            $err = error_get_last();
            $entry = date('Y-m-d H:i:s') . " | Telegram API request failed (file_get_contents returned false)\n";
            $entry .= "Method: $method\nURL: $url\nPayload: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";
            $entry .= "PHP error: " . json_encode($err) . "\n\n";
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
    function sendToTelegram($message, $chatId = TELEGRAM_CHAT_ID) {
        if (is_array($message)) {
            $data = $message;
        } else {
            $data = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];
        }
        return callTelegramMethod('sendMessage', $data, $chatId);
    }
}
?>