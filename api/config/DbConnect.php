<?php
$host = "localhost";
$user = "root";
$password = "951753bs";
$dbname = "scandiweb";

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}
