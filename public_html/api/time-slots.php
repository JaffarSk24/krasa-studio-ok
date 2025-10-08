<?php
// api/time-slots.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Разрешаем и GET, и POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получаем дату из POST или GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $date = $input['date'] ?? null;
} else {
    $date = $_GET['date'] ?? null;
}

if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode([]); // просто пустой массив вместо ошибки
    exit;
}

// Генерируем слоты с 09:00 до 20:00 каждый час
$slots = [];
for ($h = 9; $h <= 20; $h++) {
    $slots[] = sprintf('%02d:00', $h);
}

// Загружаем заблокированные слоты
$blockedFile = __DIR__ . '/../data/blocked_slots.txt';
$blocked = [];

if (file_exists($blockedFile)) {
    $lines = file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $line)) {
            $blocked[] = $line;
        }
    }
}

// Фильтруем доступные слоты
$available = [];
foreach ($slots as $time) {
    $full = "$date $time";
    if (!in_array($full, $blocked)) {
        $available[] = ['time' => $time];
    }
}

echo json_encode($available);
?>