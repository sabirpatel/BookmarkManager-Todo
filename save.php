<?php
session_start();

if (empty($_SESSION['authenticated'])) {
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
