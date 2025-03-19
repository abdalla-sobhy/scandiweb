<?php
require_once 'AbstractModel.php';

class ProductModel extends AbstractModel {

  private function removeTypename($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if ($key === '__typename') {
                unset($data[$key]);
            } else {
                $data[$key] = $this->removeTypename($value);
            }
        }
    }
    return $data;
}

// Process a field that may be a JSON string or an array.
private function processField($fieldValue) {
    if (is_array($fieldValue)) {
        return $this->removeTypename($fieldValue);
    } elseif (is_string($fieldValue)) {
        $decoded = json_decode($fieldValue, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new \RuntimeException("Invalid JSON: " . json_last_error_msg());
        }
        return $this->removeTypename($decoded);
    }
    return [];
}

private function processProduct($product) {
    if (!is_array($product)) {
        return $product;
    }
    
    // Process attributes.
    if (isset($product['attributes'])) {
        $product['attributes'] = $this->processField($product['attributes']);
    } else {
        $product['attributes'] = [];
    }
    
    // Process gallery.
    if (isset($product['gallery'])) {
        $product['gallery'] = $this->processField($product['gallery']);
    } else {
        $product['gallery'] = [];
    }
    
    // Process prices.
    if (isset($product['prices'])) {
        $product['prices'] = $this->processField($product['prices']);
    } else {
        $product['prices'] = [];
    }
    
    return $product;
}

public function getAll() {
    $stmt = $this->pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
    $result = [];
    foreach ($products as $product) {
        try {
            $result[] = $this->processProduct($product);
        } catch (\Exception $e) {
            error_log("Error processing product with id {$product['id']}: " . $e->getMessage());
            // Return a default product object to satisfy GraphQL non-null constraints.
            $result[] = [
                'id'         => $product['id'] ?? '',
                'name'       => $product['name'] ?? 'Unknown',
                'attributes' => [],
                'gallery'    => [],
                'prices'     => []
            ];
        }
    }
    return $result;
}

public function getById($id) {
    $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        return null;
    }
    try {
        return $this->processProduct($product);
    } catch (\Exception $e) {
        error_log("Error processing product with id {$id}: " . $e->getMessage());
        return [
            'id'         => $id,
            'name'       => 'Unknown',
            'attributes' => [],
            'gallery'    => [],
            'prices'     => []
        ];
    }
}

public function getByCategory($category) {
    if ($category === "all") {
        $stmt = $this->pdo->query("SELECT * FROM products");
    } else {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE category = ?");
        $stmt->execute([$category]);
    }
    $products = $stmt->fetchAll();
    $result = [];
    foreach ($products as $product) {
        try {
            $result[] = $this->processProduct($product);
        } catch (\Exception $e) {
            error_log("Error processing product with id {$product['id']}: " . $e->getMessage());
            $result[] = [
                'id'         => $product['id'] ?? '',
                'name'       => $product['name'] ?? 'Unknown',
                'attributes' => [],
                'gallery'    => [],
                'prices'     => []
            ];
        }
    }
    return $result;
}
}