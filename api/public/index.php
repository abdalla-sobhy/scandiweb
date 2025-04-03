<?php
// Helper function to set a header only if it hasn't been set already.
function set_unique_header($headerName, $headerValue) {
    // Here I get the list of headers that already set.
    $currentHeaders = headers_list();
    foreach ($currentHeaders as $header) {
        // Check if the header has already been sent (case-insensitive).
        if (stripos($header, $headerName . ":") === 0) {
            return;
        }
    }
    header("$headerName: $headerValue");
}

set_unique_header("Access-Control-Allow-Origin", "*");
set_unique_header("Access-Control-Allow-Headers", "Content-Type");
set_unique_header("Access-Control-Allow-Methods", "POST, GET, OPTIONS");

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
