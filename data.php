<?php
require __DIR__ . '/db-config.php';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(503);
    exit('DB connection failed');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    $stmt = $pdo->query('SELECT data FROM app_data WHERE id = 1');
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row ? $row['data'] : '{}';

} elseif ($method === 'POST') {
    $input = file_get_contents('php://input');
    json_decode($input);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        exit('Invalid JSON');
    }
    $stmt = $pdo->prepare('
        INSERT INTO app_data (id, data) VALUES (1, ?)
        ON DUPLICATE KEY UPDATE data = ?, updated_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([$input, $input]);
    http_response_code(200);
    echo 'OK';

} else {
    http_response_code(405);
    exit('Method Not Allowed');
}
