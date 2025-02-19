<?php
require_once 'AttributeModel.php';

class SwatchAttributeModel extends AttributeModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'swatch');
    }
    
    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM attributes WHERE type = ?");
        $stmt->execute([$this->type]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM attributes WHERE id = ? AND type = ?");
        $stmt->execute([$id, $this->type]);
        return $stmt->fetch();
    }
}
