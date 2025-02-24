<?php
$host = "localhost";
$user = getenv('DB_USER') ?: 'app_user';
$password = getenv('DB_PASS') ?: 'strong_password_123';
$dbname = "scandiweb";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}
