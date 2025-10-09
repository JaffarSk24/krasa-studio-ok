<?php
// api/update-google-reviews-cache.php
// Запускать раз в неделю через cron: 0 3 * * 0 /usr/bin/php /path/to/api/update-google-reviews-cache.php

require_once __DIR__ . '/../includes/config.php';

if (!defined('GOOGLE_PLACE_ID') || !GOOGLE_PLACE_ID) {
    die("ERROR: GOOGLE_PLACE_ID not defined\n");
}

if (!defined('GOOGLE_API_KEY') || !GOOGLE_API_KEY) {
    die("ERROR: GOOGLE_API_KEY not defined\n");
}

$placeId = GOOGLE_PLACE_ID;
$languages = ['sk', 'ru', 'ua'];
$cacheDir = __DIR__ . '/../cache';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0775, true);
}

foreach ($languages as $lang) {
    $cacheFile = $cacheDir . '/place_' . md5($placeId . '_' . $lang) . '.json';
    
    // Удаляем старый кэш
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
        echo "Deleted old cache: $cacheFile\n";
    }
    
    // Запрашиваем свежие данные
    $fields = implode(',', [
        'name','rating','user_ratings_total','url',
        'icon','icon_mask_base_uri','icon_background_color','reviews'
    ]);
    
    $url = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query([
        'place_id' => $placeId,
        'fields'   => $fields,
        'key'      => GOOGLE_API_KEY,
        'language' => $lang
    ]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_USERAGENT => 'KrasaStudioOK/1.0'
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code === 200 && $resp) {
        file_put_contents($cacheFile, $resp);
        echo "✓ Updated cache for language: $lang\n";
    } else {
        echo "✗ Failed to update cache for language: $lang (HTTP $code)\n";
    }
    
    sleep(1); // Пауза между запросами
}

echo "\n=== Google Reviews cache update completed ===\n";