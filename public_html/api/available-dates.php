<?php
// api/available-dates.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$blockedFile = __DIR__ . '/../data/blocked_slots.txt';
$blocked = [];

// Загружаем все блокированные конкретные слоты (формат "YYYY-MM-DD HH:MM")
if (file_exists($blockedFile)) {
    $lines = file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^(\d{4}-\d{2}-\d{2}) \d{2}:\d{2}$/', $line)) {
            $blocked[] = $line; // Сохраняем полностью строку "дата время"
        }
    }
}

$available = [];
$today = new DateTime();
$end = (clone $today)->modify('+30 days');

// Перебираем все даты от сегодня до +30 дней
for ($date = clone $today; $date <= $end; $date->modify('+1 day')) {
    $dateStr = $date->format('Y-m-d');

    // Генерируем все возможные слоты на день
    $slots = [];
    for ($h = 9; $h <= 20; $h++) {
        $slots[] = sprintf('%02d:00', $h);
    }

    // Проверяем, есть ли хотя бы один слот, который не заблокирован
    $hasFree = false;
    foreach ($slots as $time) {
        $full = "$dateStr $time";
        if (!in_array($full, $blocked)) {
            $hasFree = true;
            break;
        }
    }

    // Если есть свободные слоты → добавляем день в массив доступных
    if ($hasFree) {
        $available[] = $dateStr;
    }
}

echo json_encode($available);