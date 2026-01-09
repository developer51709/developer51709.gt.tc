<?php
// translate.php - resilient Lingva wrapper with mirror fallbacks and debug output
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// Debug mode if ?debug=1
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';
if ($debug) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Input
$source = $_GET['source'] ?? 'auto';
$target = $_GET['target'] ?? 'en';
$query  = $_GET['query'] ?? '';

if ($query === '') {
    http_response_code(400);
    echo json_encode(['error' => 'No query provided', 'usage' => '/api/translate.php?source=auto&target=ru&query=Hello%20World']);
    exit;
}

$mirrors = [
    "https://lingva.ml/api/v1",
    "https://lingva.garudalinux.org/api/v1",
    "https://lingva.thedaviddelta.com/api/v1",
    "https://lingva.translate.geek.nz/api/v1",
    "https://lingva.lunar.icu/api/v1",
    "https://lingva.mint.lgbt/api/v1",
    "https://lingva.lingva.lol/api/v1",
    "https://lingva.lingva.ml/api/v1",
    "https://lingva.lingva.lunar.icu/api/v1",
    "https://lingva.lingva.translate.geek.nz/api/v1",
    "https://lingva.lingva.mint.lgbt/api/v1",
    "https://lingva.lingva.garudalinux.org/api/v1",
    "https://lingva.lingva.thedaviddelta.com/api/v1",
    "https://lingva.lingva.lingva.ml/api/v1",
    "https://labs.newpush.com/lingva/api/v1",
    "https://framagit.org/iNtEgraIR2021/lingva-translate/api/v1"
];

// Helpers
function try_curl($url, $timeout = 6) {
    if (!function_exists('curl_init')) return false;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Lingva-Proxy/1.0 (+yourdomain)');
    // Optionally relax SSL verification if you see cert issues (not recommended long-term)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['body' => $resp, 'http' => $http, 'error' => $err];
}

function try_file_get_contents($url, $timeout = 6) {
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Lingva-Proxy/1.0 (+yourdomain)\r\n",
            'timeout' => $timeout
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true
        ]
    ];
    $ctx = stream_context_create($opts);
    $resp = @file_get_contents($url, false, $ctx);
    // No direct HTTP code available here; rely on JSON parse and non-false response
    return ['body' => $resp, 'http' => null, 'error' => ($resp === false ? error_get_last() : null)];
}

// Try mirrors
$failed = [];
$translation = null;
$usedMirror = null;

foreach ($mirrors as $mirror) {
    $url = rtrim($mirror, '/') . '/' . rawurlencode($source) . '/' . rawurlencode($target) . '/' . rawurlencode($query);
    // Try cURL first
    $res = try_curl($url);
    if ($res === false) {
        // cURL not available, try file_get_contents
        $res = try_file_get_contents($url);
    }
    if ($res && !empty($res['body'])) {
        $data = json_decode($res['body'], true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['translation'])) {
            $translation = $data['translation'];
            $usedMirror = $mirror;
            break;
        } else {
            $failed[] = ['mirror' => $mirror, 'reason' => 'invalid_json_or_missing_translation', 'http' => $res['http'], 'error' => $res['error']];
        }
    } else {
        $failed[] = ['mirror' => $mirror, 'reason' => 'no_response', 'http' => $res['http'] ?? null, 'error' => $res['error'] ?? null];
    }
}

if ($translation === null) {
    http_response_code(500);
    echo json_encode([
        'error' => 'All translation mirrors failed',
        'failedMirrors' => $failed,
        'hint' => 'Enable debug=1 to show PHP errors. Check server outbound request permissions and SSL settings.'
    ]);
    exit;
}

// Use JSON_UNESCAPED_UNICODE so non-ASCII characters appear directly
echo json_encode([
    'source' => $source,
    'target' => $target,
    'query' => $query,
    'translation' => $translation,
    'mirror' => $usedMirror,
    'failedMirrors' => $failed
], JSON_UNESCAPED_UNICODE);
