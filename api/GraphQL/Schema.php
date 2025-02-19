<?php
namespace GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InputObjectType;

require_once __DIR__ . '/../Models/ProductModel.php';
require_once __DIR__ . '/../Models/CategoryModel.php';
require_once __DIR__ . '/../Models/OrderModel.php';

class SchemaBuilder {
    public static function build() {
        $categoryType = new ObjectType([
            'name' => 'Category',
            'fields' => [
                'id'   => Type::id(),
                'name' => Type::string()
            ]
        ]);
        
        $attributeItemType = new ObjectType([
            'name' => 'AttributeItem',
            'fields' => [
                'id'           => Type::id(),
                'displayValue' => Type::string(),
                'value'        => Type::string()
            ]
        ]);
        
        $attributeSetType = new ObjectType([
            'name' => 'AttributeSet',
            'fields' => [
                'id'    => Type::id(),
                'name'  => Type::string(),
                'type'  => Type::string(),
                'items' => Type::listOf($attributeItemType)
            ]
        ]);
        
        $productType = new ObjectType([
            'name' => 'Product',
            'fields' => [
                'id'          => Type::id(),
                'name'        => Type::string(),
                'description' => Type::string(),
                'category' => [
                    'type' => $categoryType,
                    'resolve' => function($product) {
                        return ['id' => null, 'name' => $product['category']];
                    }
                ],
                'attributes'  => Type::listOf($attributeSetType),
                'price'       => Type::float(),
                'currency'    => Type::string(),
                'inStock'     => Type::boolean(),
                'brand'       => Type::string(),
                'gallery' => [
                    'type' => Type::listOf(Type::string()),
                    'resolve' => function($product) {
                        if (is_string($product['gallery'])) {
                            return json_decode($product['gallery'], true);
                        }
                        return $product['gallery'];
                    }
                ]
            ]
        ]);
        
        $orderType = new ObjectType([
            'name' => 'Order',
            'fields' => [
                'id'         => Type::id(),
                'product'    => $productType,
                'attributes' => Type::listOf($attributeItemType),
                'quantity'   => Type::int()
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
                        'attributes' => Type::nonNull(Type::listOf(new ObjectType([
                            'name' => 'AttributeInput',
                            'fields' => [
                                'id'    => Type::nonNull(Type::id()),
                                'value' => Type::nonNull(Type::string())
                            ]
                        ])))
                    ],
                    'resolve' => function($root, $args) {
                        $productModel = new \ProductModel($GLOBALS['pdo']);
                        $product = $productModel->getById($args['productId']);
                        if (!$product) {
                            throw new \Exception("Product not found");
                        }
                        
                        $orderData = [
                            "product_id" => $product['id'],
                            "name"       => $product['name'],
                            "price"      => $product['price'],
                            "image"      => is_array($product['gallery']) ? $product['gallery'][0] : '',
                            "size"       => "Default",
                            "color"      => "Default",
                            "category"   => $product['category'],
                            "quantity"   => 1
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
                        'items' => Type::nonNull(Type::listOf(new InputObjectType([
                            'name' => 'OrderItemInput',
                            'fields' => [
                                'productId' => Type::nonNull(Type::id()),
                                'size' => Type::nonNull(Type::string()),
                                'color' => Type::nonNull(Type::string()),
                                'quantity' => Type::nonNull(Type::int()),
                            ]
                        ])))
                    ],
                    'resolve' => function($root, $args) {
                        $orderModel = new \OrderModel($GLOBALS['pdo']);
                        $productModel = new \ProductModel($GLOBALS['pdo']);
                        $createdOrders = [];
                        
                        foreach ($args['items'] as $item) {
                            $product = $productModel->getById($item['productId']);
                            if (!$product) {
                                throw new \Exception("Product not found: " . $item['productId']);
                            }
                            
                            $orderData = [
                                "product_id" => $product['id'],
                                "name" => $product['name'],
                                "price" => $product['price'],
                                "image" => is_array($product['gallery']) ? 
                                    $product['gallery'][0] : $product['gallery'],
                                "size" => $item['size'],
                                "color" => $item['color'],
                                "category" => $product['category'],
                                "quantity" => $item['quantity']
                            ];
                            
                            $orderModel->addOrder($orderData);
                            $createdOrders[] = $orderModel->getAll();
                        }
                        
                        return array_merge(...$createdOrders);
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