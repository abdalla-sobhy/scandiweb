<?php
$host = "db";
$user = 'app_user';
$password = 'strong_password_123';
$dbname = "scandiweb";

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql)) {
    echo "Database created or already exists.\n";
} else {
    die("Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

$requiredTables = ['categories', 'products', 'orders'];

$allTablesExist = true;
foreach ($requiredTables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $allTablesExist = false;
        break;
    }
}

if (!$allTablesExist) {
    echo "Some tables are missing. Running SQL script...\n";

    $sqlFile = __DIR__ . '/scandiweb.sql';
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile");
    }

    $sqlCommands = file_get_contents($sqlFile);

    if ($conn->multi_query($sqlCommands)) {
        echo "SQL script executed successfully.\n";
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    } else {
        echo "Warning: Some queries may have failed, but execution continued.\n";
    }
    
} else {
    echo "All tables already exist.\n";
}

$conn->close();
