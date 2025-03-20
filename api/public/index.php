<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

require_once __DIR__ . '/../vendor/autoload.php';
$pdo = require_once __DIR__ . '/../config/DbConnect.php';

use GraphQL\GraphQL as WebonyxGraphQL;
use App\GraphQL\SchemaBuilder;

try {
    $schema = SchemaBuilder::build();

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if ($input === null) {
        echo json_encode(["error" => "Invalid JSON body"]);
        exit;
    }

    $query = $input['query'];
    $variables = isset($input['variables']) ? $input['variables'] : null;

    $context = ['pdo' => $pdo];

    $result = WebonyxGraphQL::executeQuery($schema, $query, null, $context, $variables);
    $output = $result->toArray();
} catch (\Exception $e) {
    $output = ['error' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($output);
