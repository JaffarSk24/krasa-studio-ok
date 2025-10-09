<?php
$blockedFile = __DIR__ . '/../data/blocked_slots.txt';
if (!file_exists($blockedFile)) exit;

$lines = file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$today = date('Y-m-d');

$filtered = array_filter($lines, function($line) use ($today) {
    $date = substr($line, 0, 10);
    return $date >= $today;
});

file_put_contents($blockedFile, implode("\n", $filtered) . "\n");