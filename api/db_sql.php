<?php
$config = require __DIR__ . '/../db.php';

try {
    // Step 1: Create ENUM type if it doesn't exist
    $enumSql = <<<SQL
    DO \$\$
    BEGIN
        IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'payment_status_type') THEN
            CREATE TYPE payment_status_type AS ENUM ('active', 'pending', 'completed', 'failed');
        END IF;
    END
    \$\$;
    SQL;
    $pdo->exec($enumSql);

    // Step 2: Create subscriptions table
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS subscriptions (
        id UUID PRIMARY KEY,
        organization_id UUID NOT NULL,
        tier_id UUID NOT NULL,
        start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        renewal_date TIMESTAMP,
        payment_status payment_status_type,
        payment_method TEXT,
        price DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        -- Foreign keys
        CONSTRAINT fk_organization FOREIGN KEY (organization_id) REFERENCES organizations(id),
        CONSTRAINT fk_tier FOREIGN KEY (tier_id) REFERENCES tiers(tier_id)
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