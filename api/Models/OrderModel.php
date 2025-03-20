<?php
namespace App\Models;

class OrderModel extends AbstractModel {
    public function addOrder($orderData) {
        $stmt = $this->pdo->prepare("
            INSERT INTO orders 
            (product_id, name, price, image, category, attributes, quantity)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
      
        $stmt->execute([
          $orderData['product_id'],
          $orderData['name'],
          $orderData['price'],
          $orderData['image'],
          $orderData['category'],
          $orderData['attributes'] ?? json_encode([]),
          $orderData['quantity']
      ]);
      return ["message" => "Product added to cart"];
    }
    
    public function getAll() {
      $stmt = $this->pdo->query("SELECT * FROM orders");
      $orders = $stmt->fetchAll();
      
      return array_map(function($order) {
          $order['attributes'] = json_decode($order['attributes'], true) ?? [];
          return $order;
      }, $orders);
  }
    
    public function increaseQuantity(int $id): array {
        $stmt = $this->pdo->prepare("UPDATE orders SET quantity = quantity + 1 WHERE id = :id");
        $stmt->execute([":id" => $id]);
        return ["message" => "Quantity increased"];
    }
    
    public function decreaseQuantity(int $id): array {
        $stmt = $this->pdo->prepare("SELECT quantity FROM orders WHERE id = :id");
        $stmt->execute([":id" => $id]);
        $order = $stmt->fetch();
        if ($order && $order["quantity"] > 1) {
            $stmt = $this->pdo->prepare("UPDATE orders SET quantity = quantity - 1 WHERE id = :id");
            $stmt->execute([":id" => $id]);
            return ["message" => "Quantity decreased"];
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM orders WHERE id = :id");
            $stmt->execute([":id" => $id]);
            return ["message" => "Product removed from cart"];
        }
    }
}