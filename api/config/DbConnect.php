<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

function jsonError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");

    $tablesExist = $pdo->query("SHOW TABLES LIKE 'categories'")->rowCount() > 0;
    
    if (!$tablesExist) {
        $sqlFile = __DIR__ . '/scandiweb.sql';
        
        if (!file_exists($sqlFile)) {
            jsonError("SQL file not found", 500);
        }
        $sql = file_get_contents($sqlFile);
        
        $sql = preg_replace('/--.*?(\r\n|\n)/', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
    }

} catch (PDOException $e) {
    jsonError("Database initialization failed: " . $e->getMessage());
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    jsonError("Database connection failed: " . $e->getMessage());
}