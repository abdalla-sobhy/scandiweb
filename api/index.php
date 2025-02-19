<?php

require_once __DIR__ . '/vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/config/' . $class . '.php',
        __DIR__ . '/Models/' . $class . '.php',
        __DIR__ . '/GraphQL/' . $class . '.php'
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config/DbConnect.php';

$requestUri = $_SERVER['REQUEST_URI'];

if (strpos($requestUri, '/graphql') === 0) {
    error_log("Handling GraphQL request");

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $query = $input['query'] ?? '';
    $variables = $input['variables'] ?? null;

    $schema = \GraphQL\SchemaBuilder::build();

    try {
        $result = \GraphQL\GraphQL::executeQuery($schema, $query, null, null, $variables);
        echo json_encode($result->toArray());
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
} else {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET' && isset($_GET['id'])) {
        $productModel = new ProductModel($pdo);
        $id = $_GET['id'] ?? '';
        $product = $productModel->getById($id);
        echo json_encode($product ?: ["error" => "Product not found"]);
    } elseif ($method === 'POST' && isset($_GET['order'])) {
        $data = json_decode(file_get_contents("php://input"), true);
        $orderModel = new OrderModel($pdo);
        $response = $orderModel->addOrder($data);
        echo json_encode($response);
    } elseif ($method === 'GET' && isset($_GET['cart'])) {
        $orderModel = new OrderModel($pdo);
        $cartProducts = $orderModel->getAll();
        echo json_encode($cartProducts);
    } elseif ($method === 'POST' && isset($_GET['increase'])) {
        $data = json_decode(file_get_contents("php://input"), true);
        $orderModel = new OrderModel($pdo);
        $response = $orderModel->increaseQuantity($data["id"]);
        echo json_encode($response);
    } elseif ($method === 'POST' && isset($_GET['decrease'])) {
        $data = json_decode(file_get_contents("php://input"), true);
        $orderModel = new OrderModel($pdo);
        $response = $orderModel->decreaseQuantity($data["id"]);
        echo json_encode($response);
    } else {
        $productModel = new ProductModel($pdo);
        $category = $_GET['category'] ?? '';
        $products = $productModel->getByCategory($category);
        echo json_encode($products);
    }
}