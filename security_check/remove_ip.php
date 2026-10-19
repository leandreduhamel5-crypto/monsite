<?php
session_start();
require_once dirname(__DIR__) . '/antibot.php';

// Validate MASTER session
if (!isset($_SESSION['MASTER']) || $_SESSION['MASTER'] !== true) {
    header("HTTP/1.0 404 Not Found");
    exit;
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