<?php
namespace GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InputObjectType;

require_once __DIR__ . '/../Models/ProductModel.php';
require_once __DIR__ . '/../Models/CategoryModel.php';
require_once __DIR__ . '/../Models/OrderModel.php';

// Define input types once to avoid naming conflicts.
$attributeInputType = new InputObjectType([
    'name' => 'AttributeInput',
    'fields' => [
        'id'    => Type::nonNull(Type::id()),
        'value' => Type::nonNull(Type::string())
    ]
]);

$orderItemInputType = new InputObjectType([
    'name' => 'OrderItemInput',
    'fields' => [
        'productId'  => Type::nonNull(Type::id()),
        'attributes' => Type::nonNull(Type::listOf($attributeInputType)),
        'quantity'   => Type::nonNull(Type::int())
    ]
]);

$cartAttributesInputType = Type::listOf($attributeInputType);

class SchemaBuilder {
    public static function build() {
        global $attributeInputType, $orderItemInputType; // bring in the globally defined input types
        
        $categoryType = new ObjectType([
            'name' => 'Category',
            'fields' => [
                'id'   => Type::id(),
                'name' => Type::string(),
                '__typename' => [
                    'type' => Type::string(),
                    'resolve' => function() { return 'Category'; }
                ]
            ]
        ]);
        
        $attributeItemType = new ObjectType([
            'name' => 'Attribute',
            'fields' => [
                'id'           => Type::id(),
                'displayValue' => Type::string(),
                'value'        => Type::string(),
                '__typename' => [
                    'type' => Type::string(),
                    'resolve' => function() { return 'Attribute'; }
                ]
            ]
        ]);
        
        $attributeSetType = new ObjectType([
            'name' => 'AttributeSet',
            'fields' => [
                'id'    => Type::id(),
                'name'  => Type::string(),
                'type'  => Type::string(),
                'items' => Type::listOf($attributeItemType),
                '__typename' => [
                    'type' => Type::string(),
                    'resolve' => function() { return 'AttributeSet'; }
                ]
            ]
        ]);
        
        $currencyType = new ObjectType([
            'name' => 'Currency',
            'fields' => [
                'label' => Type::string(),
                'symbol' => Type::string(),
                '__typename' => [
                    'type' => Type::string(),
                    'resolve' => function() { return 'Currency'; }
                ]
            ]
        ]);
        
        $priceType = new ObjectType([
            'name' => 'Price',
            'fields' => [
                'amount' => Type::float(),
                'currency' => $currencyType,
                '__typename' => [
                    'type' => Type::string(),
                    'resolve' => function() { return 'Price'; }
                ]
            ]
        ]);
        
        $productType = new ObjectType([
            'name' => 'Product',
            'fields' => [
                'id'          => Type::nonNull(Type::id()),
                'name'        => Type::nonNull(Type::string()),
                'description' => Type::string(),
                'category' => [
                    'type' => $categoryType,
                    'resolve' => function($product) use ($categoryType) {
                        $categoryModel = new \CategoryModel($GLOBALS['pdo']);
                        $categoryName = trim($product['category']);
                        $category = $categoryModel->getByName($categoryName);
                        if (!$category) {
                            error_log("Category '$categoryName' not found for product {$product['id']}");
                            return ['id' => -1, 'name' => 'Unknown'];
                        }
                        return $category;
                    }
                ],
                'attributes'  => Type::listOf($attributeSetType),
                'prices' => [
                    'type' => Type::listOf($priceType),
                    'resolve' => function($product) {
                        $prices = $product['prices'] ?? [];
                        if (isset($prices) && is_string($prices)) {
                            $prices = json_decode($prices, true);
                        }
                        if (is_array($prices)) {
                            foreach($prices as &$price) {
                                if(isset($price['__typename'])) {
                                    unset($price['__typename']);
                                }
                                if(isset($price['currency']) && is_array($price['currency']) && isset($price['currency']['__typename'])) {
                                    unset($price['currency']['__typename']);
                                }
                            }
                        }
                        return $prices;
                    }
                ],
                'inStock'     => Type::boolean(),
                'brand'       => Type::string(),
                'gallery' => [
                    'type' => Type::listOf(Type::string()),
                    'resolve' => function($product) {
                        $gallery = $product['gallery'] ?? [];
                        if (isset($gallery) && is_string($gallery)) {
                            $gallery = json_decode($gallery, true);
                        }
                        return $gallery;
                    }
                ],
                '__typename' => [
                    'type' => Type::string(),
                    'resolve' => function() { return 'Product'; }
                ]
            ]
        ]);
        
        $orderType = new ObjectType([
            'name' => 'Order',
            'fields' => [
                'id'         => Type::id(),
                'product'    => $productType,
                'attributes' => Type::listOf($attributeItemType),
                'quantity'   => Type::int(),
                '__typename' => [
                    'type' => Type::string(),
                    'resolve' => function() { return 'Order'; }
                ]
            ]
        ]);
        
        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'categories' => [
                    'type' => Type::listOf($categoryType),
                    'resolve' => function() {
                        $categoryModel = new \CategoryModel($GLOBALS['pdo']);
                        return $categoryModel->getAll();
                    }
                ],
                'products' => [
                    'type' => Type::listOf($productType),
                    'args' => [
                        'category' => Type::string()
                    ],
                    'resolve' => function($root, $args) {
                        $productModel = new \ProductModel($GLOBALS['pdo']);
                        if (!empty($args['category'])) {
                            return $productModel->getByCategory($args['category']);
                        }
                        return $productModel->getAll();
                    }
                ],
                'product' => [
                    'type' => $productType,
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => function($root, $args) {
                        $productModel = new \ProductModel($GLOBALS['pdo']);
                        return $productModel->getById($args['id']);
                    }
                ],
                'cart' => [
                    'type' => Type::listOf($orderType),
                    'resolve' => function() {
                        $orderModel = new \OrderModel($GLOBALS['pdo']);
                        return $orderModel->getAll();
                    }
                ]
            ]
        ]);
        
        $mutationType = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'addToCart' => [
                    'type' => $orderType,
                    'args' => [
                        'productId'  => Type::nonNull(Type::id()),
                        'attributes' => Type::nonNull(Type::listOf($attributeInputType))
                    ],
                    'resolve' => function($root, $args) {
                        $productModel = new \ProductModel($GLOBALS['pdo']);
                        $product = $productModel->getById($args['productId']);
                        if (!$product) {
                            throw new \Exception("Product not found");
                        }
                        $priceData = $product['prices'][0] ?? ['amount' => 0, 'currency' => ['label' => 'USD']];
                        $orderData = [
                            "product_id" => $product['id'],
                            "name" => $product['name'],
                            "price" => $priceData['amount'],
                            "currency" => $priceData['currency']['label'],
                            "image" => is_array($product['gallery']) ? $product['gallery'][0] : '',
                            "category" => $product['category'],
                            "quantity" => 1
                        ];
                        $orderModel = new \OrderModel($GLOBALS['pdo']);
                        $orderModel->addOrder($orderData);
                        $orders = $orderModel->getAll();
                        return end($orders);
                    }
                ],
                'placeOrder' => [
                    'type' => Type::listOf($orderType),
                    'args' => [
                        'items' => Type::nonNull(Type::listOf($orderItemInputType))
                    ],
                    'resolve' => function($root, $args) {
                        $orderModel = new \OrderModel($GLOBALS['pdo']);
                        $productModel = new \ProductModel($GLOBALS['pdo']);
                        foreach ($args['items'] as $item) {
                            $product = $productModel->getById($item['productId']);
                            if (!$product) {
                                throw new \Exception("Product not found: " . $item['productId']);
                            }
                            $attributes = [];
                            foreach ($item['attributes'] as $attr) {
                                $attributes[] = [
                                    'id' => $attr['id'],
                                    'value' => $attr['value']
                                ];
                            }
                            $orderData = [
                                "product_id" => $product['id'],
                                "name" => $product['name'],
                                "price" => isset($product['prices'][0]['amount']) ? $product['prices'][0]['amount'] : 0,
                                "image" => is_array($product['gallery']) ? $product['gallery'][0] : '',
                                "category" => $product['category'],
                                "attributes" => json_encode($attributes),
                                "quantity" => $item['quantity']
                            ];
                            $orderModel->addOrder($orderData);
                        }
                        return $orderModel->getAll();
                    }
                ]
            ]
        ]);
        
        return new Schema([
            'query' => $queryType,
            'mutation' => $mutationType
        ]);
    }
}
