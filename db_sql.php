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

try {
    // SQL: Create users table if it doesn't exist
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS link_token (
        sn INT AUTO_INCREMENT PRIMARY KEY,  -- Unique serial number
        id UUID NOT NULL,     -- Must match the phone format in users table
        token VARCHAR(500) NOT NULL UNIQUE,  -- Secure token (hashed or random)
        is_used BOOLEAN DEFAULT FALSE,  -- Track if token has been used
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Timestamp for expiry tracking
        FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE,  -- Ensure referential integrity
        INDEX idx_id (id)  -- Composite index for faster lookups
    );
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
