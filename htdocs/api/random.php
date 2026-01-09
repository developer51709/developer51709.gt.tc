<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// --- Input handling ---
$type = $_GET['type'] ?? 'fact';
$validTypes = ['fact','joke','quote'];

if (!in_array($type, $validTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type. Must be fact, joke, or quote']);
    exit;
}

// --- Load dataset ---
$file = __DIR__ . "/data/{$type}s.json"; // facts.json, jokes.json, quotes.json

if (!file_exists($file)) {
    http_response_code(500);
    echo json_encode(['error' => "Data file for {$type}s not found"]);
    exit;
}

$list = json_decode(file_get_contents($file), true);

if (!$list || !is_array($list)) {
    http_response_code(500);
    echo json_encode(['error' => "Invalid data format in {$type}s.json"]);
    exit;
}

// --- Pick random entry ---
$result = $list[array_rand($list)];

// --- Output ---
echo json_encode([
    'type' => $type,
    'result' => $result
]);