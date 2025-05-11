<?php
$config = require __DIR__ . '/../db.php';

try {
    // Step 2: Create subscriptions table
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS organization (
        id UUID PRIMARY KEY,
        name TEXT NOT NULL,
        billing_email TEXT,
        billing_address TEXT,
        phone_number TEXT,
        website TEXT,
        use_warehouse_receipt_system BOOLEAN DEFAULT TRUE,
        use_dashboard  BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
catch (Exception $e) {
    echo "❌ An error occurred: " . $e->getMessage();
    exit;
}