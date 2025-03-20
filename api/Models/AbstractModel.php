<?php
namespace App\Models;

abstract class AbstractModel {
    protected $pdo;
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
}