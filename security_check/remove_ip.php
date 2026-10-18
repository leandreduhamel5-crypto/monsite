<?php
session_start();

// Antibot check logic
if (!isset($_SESSION['STEP_CHAIN']) || $_SESSION['STEP_CHAIN'] < 3) {
    header("HTTP/1.0 404 Not Found"); exit;
}
if (!isset($_SESSION['qsdfqsdfqsdfqsdf123123']) || $_SESSION['qsdfqsdfqsdfqsdf123123'] !== true) {
    header("HTTP/1.0 404 Not Found"); exit;
}

//   _____         _ __      __     
//  / ____|  /\   | |\ \    / /\    
// | (___   /  \  | | \ \  / /  \   
//  \___ \ / /\ \ | |  \ \/ / /\ \  
//  ____) / ____ \| |___\  / ____ \ 
// |_____/_/    \_\______\/_/    \_\

$userIP = $_SERVER['REMOTE_ADDR']; 

$ipListFile = './ip_list.txt';

if (file_exists($ipListFile)) {
    $ipList = file($ipListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $filteredIPList = array_filter($ipList, function($line) use ($userIP) {
        return strpos($line, $userIP) === false;
    });

    file_put_contents($ipListFile, implode(PHP_EOL, $filteredIPList));
}

echo 'IP removed';
?>