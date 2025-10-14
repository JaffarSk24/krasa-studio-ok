<?php
// api/google-places-proxy.php
// Прокси Google Place Details с файловым кэшем на 7 дней
// Всегда запрашиваем оригинальные тексты отзывов (reviews_no_translations=true),
// при этом язык используем только для стабильного ключа кэша.

header('Content-Type: application/json; charset=utf-8');

$placeId = $_GET['place_id'] ?? '';
$langRaw = $_GET['language'] ?? '';        // что пришло с фронта
$lang    = $langRaw ? strtolower($langRaw) : 'sk'; // язык только для ключа кэша

if (!$placeId) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_place_id']);
    exit;
}

// Подтягиваем ключ из includes/config.php
require_once __DIR__ . '/../includes/config.php';
if (!defined('GOOGLE_API_KEY') || !GOOGLE_API_KEY) {
    http_response_code(500);
    echo json_encode(['error' => 'missing_api_key']);
    exit;
}

// Кэш
$cacheDir = __DIR__ . '/../cache';
if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0775, true); }
$cacheFile = $cacheDir . '/place_' . md5($placeId . '_' . $lang) . '.json';
$ttl = 7 * 24 * 60 * 60; // 7 дней

if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
    readfile($cacheFile);
    exit;
}

$fields = implode(',', [
    'name','rating','user_ratings_total','url',
    'icon','icon_mask_base_uri','icon_background_color','reviews'
]);

// Формируем запрос к Google: используем язык (для возможной сортировки/метаданных),
// но главное — просим вернуть оригинальные тексты отзывов
$query = [
    'place_id'                 => $placeId,
    'fields'                   => $fields,
    'key'                      => GOOGLE_API_KEY,
    'language'                 => $lang,              // стабилизирует кэш
    'reviews_no_translations'  => 'true'              // оригинальные тексты отзывов
];

$url = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query($query);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_USERAGENT => 'KrasaStudioOK/1.0'
]);
$resp = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $code >= 400 || !$resp) {
    http_response_code(502);
    echo json_encode(['error' => 'upstream_failed', 'detail' => $err, 'code' => $code]);
    exit;
}

// Сохраняем кэш и отдаём ответ
@file_put_contents($cacheFile, $resp);
echo $resp;