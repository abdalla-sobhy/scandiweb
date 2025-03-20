<?php
namespace App\GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InputObjectType;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\OrderModel;

class SchemaBuilder {
    public static function build() {
        // Define input types
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
                    'resolve' => function($product, $args, $context) {
                        $categoryModel = new CategoryModel($context['pdo']);
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
                        if (is_string($prices)) {
                            $prices = json_decode($prices, true);
                        }
                        foreach($prices as &$price) {
                            unset($price['__typename']);
                            if(isset($price['currency']['__typename'])) {
                                unset($price['currency']['__typename']);
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
                        if (is_string($gallery)) {
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
                    'resolve' => function($root, $args, $context) {
                        return (new CategoryModel($context['pdo']))->getAll();
                    }
                ],
                'products' => [
                    'type' => Type::listOf($productType),
                    'args' => [
                        'category' => Type::string()
                    ],
                    'resolve' => function($root, $args, $context) {
                        $model = new ProductModel($context['pdo']);
                        return isset($args['category']) 
                            ? $model->getByCategory($args['category'])
                            : $model->getAll();
                    }
                ],
                'product' => [
                    'type' => $productType,
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => function($root, $args, $context) {
                        return (new ProductModel($context['pdo']))->getById($args['id']);
                    }
                ],
                'cart' => [
                    'type' => Type::listOf($orderType),
                    'resolve' => function($root, $args, $context) {
                        return (new OrderModel($context['pdo']))->getAll();
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
                    'resolve' => function($root, $args, $context) {
                        $product = (new ProductModel($context['pdo']))->getById($args['productId']);
                        if (!$product) {
                            throw new \Exception("Product not found");
                        }
                        
                        $orderData = [
                            "product_id" => $product['id'],
                            "name" => $product['name'],
                            "price" => $product['prices'][0]['amount'] ?? 0,
                            "image" => $product['gallery'][0] ?? '',
                            "category" => $product['category'],
                            "quantity" => 1
                        ];
                        
                        (new OrderModel($context['pdo']))->addOrder($orderData);
                        $orders = (new OrderModel($context['pdo']))->getAll();
                        return end($orders);
                    }
                ],
                'placeOrder' => [
                    'type' => Type::listOf($orderType),
                    'args' => [
                        'items' => Type::nonNull(Type::listOf($orderItemInputType))
                    ],
                    'resolve' => function($root, $args, $context) {
                        $orderModel = new OrderModel($context['pdo']);
                        $productModel = new ProductModel($context['pdo']);
                        
                        foreach ($args['items'] as $item) {
                            $product = $productModel->getById($item['productId']);
                            if (!$product) {
                                throw new \Exception("Product not found: " . $item['productId']);
                            }
                            
                            $orderData = [
                                "product_id" => $product['id'],
                                "name" => $product['name'],
                                "price" => $product['prices'][0]['amount'] ?? 0,
                                "image" => $product['gallery'][0] ?? '',
                                "category" => $product['category'],
                                "attributes" => json_encode($item['attributes']),
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
