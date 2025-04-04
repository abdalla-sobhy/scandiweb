<?php

namespace App\Controller;

use GraphQL\GraphQL as WebonyxGraphQL;
use App\GraphQL\SchemaBuilder;

class GraphQL {
    public static function handle($vars) {
        header('Content-Type: application/json');
        
        $pdo = require __DIR__ . '/../../config/DbConnect.php';
        
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
            
            echo json_encode($result->toArray());
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
