<?php
// Function to ensure unique headers and only 1 use of each header in my requests
function set_unique_header($headerName, $headerValue) {
    foreach (headers_list() as $header) {
        if (stripos($header, "$headerName:") === 0) {
            return;
        }
    }
    header("$headerName: $headerValue");
}

set_unique_header("Access-Control-Allow-Origin", "*");
set_unique_header("Access-Control-Allow-Headers", "Content-Type, Authorization");
set_unique_header("Access-Control-Allow-Methods", "POST, GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('POST', '/graphql', [App\Controller\GraphQL::class, 'handle']);
});

$routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], '/graphql');

switch ($routeInfo[0]) {
  case FastRoute\Dispatcher::NOT_FOUND:
      http_response_code(404);
      echo json_encode([ "error" => "Not Found" ]);
      break;

  case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
      http_response_code(405);
      echo json_encode([ "error" => "Method Not Allowed" ]);
      break;

  case FastRoute\Dispatcher::FOUND:
      [$class, $method] = $routeInfo[1];
      $vars = $routeInfo[2];
      $controller = new $class();
      $response = $controller->$method($vars);
      
      if ($response != null) {
        echo json_encode($response);
      }
      
      exit;
}