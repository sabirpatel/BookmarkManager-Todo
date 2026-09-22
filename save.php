<?php
$PASS   = '2608';
$SECRET = hash('sha256', $PASS . 'bm_v1');
$COOKIE = 'bm_auth';

if (!isset($_COOKIE[$COOKIE]) || $_COOKIE[$COOKIE] !== $SECRET) {
    http_response_code(401);
    exit('Unauthorized');
}

$data = file_get_contents('php://input');
if (!$data) {
    http_response_code(400);
    exit('No data');
}

// Validate it's valid JSON before saving
json_decode($data);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    exit('Invalid JSON');
}

file_put_contents(__DIR__ . '/bookmarks.json', $data);
echo 'OK';
