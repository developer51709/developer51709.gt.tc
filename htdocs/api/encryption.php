<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// --- Helper functions ---
function caesar($s, $shift, $decrypt=false) {
    $shift = ($decrypt ? -$shift : $shift) % 26;
    $out = '';
    for ($i=0; $i<strlen($s); $i++) {
        $c = $s[$i];
        if ($c >= 'a' && $c <= 'z') {
            $base = ord('a');
            $out .= chr(($base + (ord($c)-$base + $shift + 26) % 26));
        } elseif ($c >= 'A' && $c <= 'Z') {
            $base = ord('A');
            $out .= chr(($base + (ord($c)-$base + $shift + 26) % 26));
        } else {
            $out .= $c;
        }
    }
    return $out;
}

function vigenere($s, $key, $decrypt=false) {
    if ($key === '') return $s;
    $out = ''; $ki = 0; $kl = strlen($key);
    for ($i=0; $i<strlen($s); $i++) {
        $c = $s[$i]; $k = $key[$ki % $kl];
        if (ctype_alpha($c)) {
            $shift = (ord(strtoupper($k)) - ord('A')) % 26;
            if ($decrypt) $shift = -$shift;
            if (ctype_lower($c)) {
                $base = ord('a');
                $out .= chr($base + (ord($c)-$base + $shift + 26) % 26);
            } else {
                $base = ord('A');
                $out .= chr($base + (ord($c)-$base + $shift + 26) % 26);
            }
            $ki++;
        } else {
            $out .= $c;
        }
    }
    return $out;
}

function xorBytes($data, $key) {
    if ($key === '') return $data;
    $out = '';
    $kl = strlen($key);
    for ($i=0; $i<strlen($data); $i++) {
        $out .= chr(ord($data[$i]) ^ ord($key[$i % $kl]));
    }
    return $out;
}

// --- Input handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_GET;
}

$mode        = $input['mode'] ?? null;
$text        = $input['text'] ?? '';
$caesarShift = intval($input['caesarShift'] ?? 0);
$vigenereKey = preg_replace('/[^A-Za-z]/', '', $input['vigenereKey'] ?? '');
$xorKey      = $input['xorKey'] ?? '';
$useCaesar   = filter_var($input['useCaesar'] ?? false, FILTER_VALIDATE_BOOLEAN);
$useVigenere = filter_var($input['useVigenere'] ?? false, FILTER_VALIDATE_BOOLEAN);
$useXor      = filter_var($input['useXor'] ?? false, FILTER_VALIDATE_BOOLEAN);
$useBase64   = filter_var($input['useBase64'] ?? false, FILTER_VALIDATE_BOOLEAN);

if (!$mode || !in_array($mode, ['encrypt','decrypt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Mode must be encrypt or decrypt']);
    exit;
}

// --- Processing ---
if ($mode === 'encrypt') {
    $result = $text;
    if ($useCaesar)   $result = caesar($result, $caesarShift, false);
    if ($useVigenere) $result = vigenere($result, $vigenereKey, false);
    if ($useXor)      $result = xorBytes($result, $xorKey);
    if ($useBase64)   $result = base64_encode($result);
} else {
    $result = $text;
    if ($useBase64) {
        $decoded = base64_decode($result, true);
        if ($decoded === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid base64']);
            exit;
        }
        $result = $decoded;
    }
    if ($useXor)      $result = xorBytes($result, $xorKey);
    if ($useVigenere) $result = vigenere($result, $vigenereKey, true);
    if ($useCaesar)   $result = caesar($result, $caesarShift, true);
}

echo json_encode(['result' => $result]);