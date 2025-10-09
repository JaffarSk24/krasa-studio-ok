<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $date = $input['date'] ?? null;
} else {
    $date = $_GET['date'] ?? null;
}

// Проверяем формат даты d-m-Y
if (!$date || !preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
    echo json_encode([]);
    exit;
}

// Преобразуем дату в ISO для сравнения с заблокированными слотами
$dateObj = DateTime::createFromFormat('d-m-Y', $date);
if (!$dateObj) {
    echo json_encode([]);
    exit;
}
$dateIso = $dateObj->format('Y-m-d');

// Генерируем слоты с 09:00 до 20:30 с шагом 30 минут
$slots = [];
for ($h = 9; $h <= 20; $h++) {
    $slots[] = sprintf('%02d:00', $h);
    if ($h !== 20) {
        $slots[] = sprintf('%02d:30', $h);
    }
}

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

$available = [];
foreach ($slots as $time) {
    $full = "$dateIso $time";
    if (!in_array($full, $blocked)) {
        $available[] = ['time' => $time];
    }
}

echo json_encode($available);
?>