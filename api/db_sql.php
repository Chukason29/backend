<?php
$config = require __DIR__ . '/../db.php';

try {
    // SQL: Create users table if it doesn't exist
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS link_token (
        email TEXT NOT NULL PRIMARY KEY,     -- Must match the phone format in users table
        token VARCHAR(500) NOT NULL UNIQUE,  -- Secure token (hashed or random)
        is_used BOOLEAN DEFAULT FALSE,  -- Track if token has been used
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Timestamp for expiry tracking
        FOREIGN KEY (email) REFERENCES users(email) ON DELETE CASCADE  -- Ensure referential integrity
    );
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
