<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/GraphQL/Schema.php';
require_once __DIR__ . '/config/DbConnect.php'; 

use GraphQL\GraphQL;
use GraphQL\Error\FormattedError;

$GLOBALS['pdo'] = $pdo;
$tablesExist = true;
try {
    $stmt = $pdo->query("SHOW TABLES");
    if ($stmt->rowCount() < 3) {
        $tablesExist = false;
    }
} catch (PDOException $e) {
    // Tables don't exist or there's an error
}

// If tables don't exist, run the SQL script
if (!$tablesExist) {
    require_once __DIR__ . '/migrations/run_sql_script.php';
}


try {
    $schema = \GraphQL\SchemaBuilder::build();

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if($input===null){
        echo json_encode(["error" => "Invalid JSON body"]);
    exit;
    }

    $query = $input['query'];
    $variables = isset($input['variables']) ? $input['variables'] : null;

    $result = GraphQL::executeQuery($schema, $query, null, null, $variables);
    $output = $result->toArray();
} catch (\Exception $e) {
    $output = [
        'errors' => [
            FormattedError::createFromException($e)
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($output);