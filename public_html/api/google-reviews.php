<?php
// api/google-reviews.php
header('Content-Type: application/json; charset=utf-8');

// TODO: замените на свои значения
$PLACE_ID = 'ChIJURI_hqWPbEcRdG8m3Yd9dZs';
$API_KEY  = 'AIzaSyBuKEzgCtyZVFGP9xsc7xKqsBdONvtynRA';

// Кэш в файл на 12 часов
$CACHE_FILE = __DIR__ . '/cache_google_reviews.json';
$CACHE_TTL  = 12 * 60 * 60;

if (file_exists($CACHE_FILE) && (time() - filemtime($CACHE_FILE)) < $CACHE_TTL) {
    readfile($CACHE_FILE);
    exit;
}

$endpoint = 'https://maps.googleapis.com/maps/api/place/details/json';
$params = http_build_query([
    'place_id' => $PLACE_ID,
    'fields'   => 'name,rating,user_ratings_total,reviews,url',
    'language' => 'sk', // можно ru/uk при необходимости
    'key'      => $API_KEY,
]);

$ch = curl_init($endpoint . '?' . $params);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 12,
]);
$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($resp === false || $http !== 200) {
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'fetch_failed','http'=>$http,'curl'=>$err], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($resp, true);
if (($data['status'] ?? '') !== 'OK') {
    http_response_code(502);
    echo json_encode([
        'ok'=>false,
        'error'=>'api_status',
        'status'=>$data['status'] ?? 'unknown',
        'message'=>$data['error_message'] ?? null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Нормализуем
$r = $data['result'];
$out = [
    'ok' => true,
    'place' => [
        'name'  => $r['name'] ?? '',
        'rating'=> $r['rating'] ?? null,
        'total' => $r['user_ratings_total'] ?? null,
        'url'   => $r['url'] ?? null,
    ],
    'reviews' => array_map(function($v){
        return [
            'author'     => $v['author_name'] ?? '',
            'avatar'     => $v['profile_photo_url'] ?? '',
            'rating'     => $v['rating'] ?? null,
            'time_rel'   => $v['relative_time_description'] ?? '',
            'text'       => $v['text'] ?? '',
            'unix_time'  => isset($v['time']) ? (int)$v['time'] : null,
            'language'   => $v['language'] ?? null,
            'translated' => $v['translated'] ?? false,
        ];
    }, $r['reviews'] ?? []),
];

// Кладём в кэш
@file_put_contents($CACHE_FILE, json_encode($out, JSON_UNESCAPED_UNICODE));

// Отдаём
echo json_encode($out, JSON_UNESCAPED_UNICODE);