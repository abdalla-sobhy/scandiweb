<?php
require_once 'AbstractModel.php';

class OrderModel extends AbstractModel {
    public function addOrder(array $data): array {
        $stmt = $this->pdo->prepare("SELECT id, quantity FROM orders WHERE product_id = :product_id AND size = :size AND color = :color");
        $stmt->execute([
            ":product_id" => $data["product_id"],
            ":size" => $data["size"],
            ":color" => $data["color"]
        ]);
        $existingOrder = $stmt->fetch();
        
        if ($existingOrder) {
            $stmt = $this->pdo->prepare("UPDATE orders SET quantity = quantity + 1 WHERE id = :id");
            $stmt->execute([":id" => $existingOrder["id"]]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO orders (product_id, name, price, image, size, color, category, quantity) VALUES (:product_id, :name, :price, :image, :size, :color, :category, :quantity)");
            $stmt->execute([
                ":product_id" => $data["product_id"],
                ":name" => $data["name"],
                ":price" => $data["price"],
                ":image" => $data["image"],
                ":size" => $data["size"],
                ":color" => $data["color"],
                ":category" => $data["category"],
                ":quantity" => $data["quantity"]
            ]);
        }
        
        return ["message" => "Product added to cart"];
    }
    
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM orders");
        return $stmt->fetchAll();
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
