<?php
$PASS   = '2608';
$SECRET = hash('sha256', $PASS . 'bm_v1');
$COOKIE = 'bm_auth';

if (!isset($_COOKIE[$COOKIE]) || $_COOKIE[$COOKIE] !== $SECRET) {
    http_response_code(401);
    exit('Unauthorized');
}

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
    $stmt = $pdo->query('SELECT data FROM app_data WHERE id = 1');
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo $row ? $row['data'] : '{}';

} elseif ($method === 'PUT') {
    $input = file_get_contents('php://input');
    json_decode($input);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        exit('Invalid JSON');
    }
    $stmt = $pdo->prepare('
        INSERT INTO app_data (id, data) VALUES (1, ?)
        ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([$input]);
    http_response_code(200);
    echo 'OK';

} else {
    http_response_code(405);
    exit('Method Not Allowed');
}
