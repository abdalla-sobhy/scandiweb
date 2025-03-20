<?php
namespace App\Models;
abstract class AttributeModel extends AbstractModel {
    protected $type;
    public function __construct(\PDO $pdo, string $type) {
        parent::__construct($pdo);
        $this->type = $type;
    }
    abstract public function getAll();
    abstract public function getById($id);
}