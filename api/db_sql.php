<?php
$config = require __DIR__ . '/../db.php';

try {
    // SQL: Create users table if it doesn't exist
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS subscriptions (
        id UUID NOT NULL PRIMARY KEY,
        FOREIGN KEY (organization_id) REFERENCES organizations(id)
        FOREIGN KEY (tier_id) REFERENCES tiers(tier_id)      
        start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        renewal_date TIMESTAMP,      
        payment_status ENUM('active','pending', 'completed', 'failed'),
        payment_method TEXT,
        price DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    SQL;

    $pdo->exec($sql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
