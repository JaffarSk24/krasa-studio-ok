<?php
$blockedFile = __DIR__ . '/../data/blocked_slots.txt';
if (!file_exists($blockedFile)) exit;

$fp = fopen($blockedFile, 'c+');
if (!$fp) exit;

flock($fp, LOCK_EX);

$lines = [];
while (($line = fgets($fp)) !== false) {
    $line = trim($line);
    if ($line === '') continue;
    $date = substr($line, 0, 10);
    $lines[] = $line;
}

$today = date('Y-m-d');

$filtered = array_filter($lines, function($line) use ($today) {
    $date = substr($line, 0, 10);
    return $date >= $today;
});

ftruncate($fp, 0);
rewind($fp);
if (!empty($filtered)) {
    fwrite($fp, implode("\n", $filtered) . "\n");
}

fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);