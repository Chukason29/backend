<?php
$config = require __DIR__ . '/../db.php';
try {
    // SQL: Create users table if it doesn't exist
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS warehouse_users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}