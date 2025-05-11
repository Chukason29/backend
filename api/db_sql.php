<?php
$config = require __DIR__ . '/../db.php';

try {
    // SQL: Create users table if it doesn't exist
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS tiers (
        tier_id UUID NOT NULL PRIMARY KEY,     -- Must match the phone format in users table
        tier_name VARCHAR(20) NOT NULL UNIQUE,  -- Secure token (hashed or random)
        max_users INT  -- Track if token has been used
    );
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
