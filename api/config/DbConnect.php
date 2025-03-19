<?php
// Set error handling before any output
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

function jsonError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

$host = "localhost";
$user = "root";
$password = "951753bs";
$dbname = "scandiweb";

try {
    // Initial connection without database
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");

    // Check if tables exist
    $tablesExist = $pdo->query("SHOW TABLES LIKE 'categories'")->rowCount() > 0;
    
    if (!$tablesExist) {
        // Execute SQL file
        $sqlFile = __DIR__ . '/scandiweb.sql';
        
        if (!file_exists($sqlFile)) {
            jsonError("SQL file not found", 500);
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Remove comments and split statements
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
    // Final connection with database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    jsonError("Database connection failed: " . $e->getMessage());
}