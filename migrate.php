<?php
/**
 * One-time migration: imports JSONBinSept21.json into MySQL.
 * Run once from the browser or CLI, then delete this file.
 *
 * Usage (browser): https://3as.us/migrate.php
 * Usage (CLI):     php migrate.php
 */

require __DIR__ . '/db-config.php';

$jsonFile = __DIR__ . '/JSONBinSept21.json';

if (!file_exists($jsonFile)) {
    die("ERROR: JSONBinSept21.json not found.\n");
}

$json = file_get_contents($jsonFile);
if ($json === false) {
    die("ERROR: Could not read JSONBinSept21.json.\n");
}

json_decode($json);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("ERROR: JSONBinSept21.json is not valid JSON: " . json_last_error_msg() . "\n");
}

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("ERROR: DB connection failed: " . $e->getMessage() . "\n");
}

// Create table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS app_data (
        id          INT          NOT NULL DEFAULT 1,
        data        LONGTEXT     NOT NULL,
        updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        CONSTRAINT chk_single_row CHECK (id = 1)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "Table 'app_data' ready.\n";

// Insert or replace the data
$stmt = $pdo->prepare("
    INSERT INTO app_data (id, data) VALUES (1, ?)
    ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = CURRENT_TIMESTAMP
");
$stmt->execute([$json]);

$rows = $stmt->rowCount();
echo "Migration complete. Rows affected: {$rows}\n";
echo "Record count: " . substr_count($json, '"title"') . " bookmarks imported.\n";
echo "\nDone! You can now delete this file and JSONBinSept21.json from the server.\n";
