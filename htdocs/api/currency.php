<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$from = $_GET['from'] ?? 'USD';
$to   = $_GET['to'] ?? 'EUR';
$amount = floatval($_GET['amount'] ?? 1);

$url = "https://api.exchangerate.host/convert?from=$from&to=$to&amount=$amount";
$response = file_get_contents($url);
if (!$response) {
  http_response_code(500);
  echo json_encode(['error'=>'Unable to fetch rates']);
  exit;
}

$data = json_decode($response, true);
echo json_encode([
  'from'=>$from,
  'to'=>$to,
  'amount'=>$amount,
  'result'=>$data['result'] ?? null
]);