<?php
require_once 'AbstractModel.php';

class CategoryModel extends AbstractModel {
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM categories");
        return $stmt->fetchAll();
    }
    
    public function getByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
}
