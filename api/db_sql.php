<?php
$config = require __DIR__ . '/../db.php';

try {
    // SQL: Create users table if it doesn't exist
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS tiers (
        tier_id UUID NOT NULL PRIMARY KEY,     
        tier_name VARCHAR(20) NOT NULL UNIQUE,  
        price DECIMAL(10, 2) NOT NULL,        
        max_users INT  NOT NULL,
        tier_description TEXT
    );
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
