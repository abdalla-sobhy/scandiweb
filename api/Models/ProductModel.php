<?php
require_once 'AbstractModel.php';

class ProductModel extends AbstractModel {
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM products");
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getByCategory($category) {
        if ($category === "all") {
            $stmt = $this->pdo->query("SELECT * FROM products");
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM products WHERE category = ?");
            $stmt->execute([$category]);
        }
        return $stmt->fetchAll();
    }
}
