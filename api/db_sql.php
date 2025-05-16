<?php
$config = require __DIR__ . '/../db.php';
try {
    // Step 2: Create subscriptions table
    $tableSql = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
        id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        email VARCHAR(255) NOT NULL UNIQUE,
        name TEXT,
        password_hash TEXT,
        organization_id UUID NOT NULL,
        role_id UUID NOT NULL,
        last_login TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        active BOOLEAN DEFAULT TRUE,
        profile_picture JSONB,
        position TEXT,
        department TEXT,
        deleted_at TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,

        -- Foreign Keys
        CONSTRAINT fk_organization FOREIGN KEY (organization_id) REFERENCES organizations(id),
        CONSTRAINT fk_role FOREIGN KEY (role_id) REFERENCES roles(id),
    );

    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
try {
    // Step 2: Create subscriptions table
    $tableSql = <<<SQL
    CREATE TABLE link_token (
        token TEXT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        is_used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}

try {
    // Step 2: Create subscriptions table
    $tableSql = <<<SQL
    CREATE TABLE organizations (
        id UUID PRIMARY KEY,
        name TEXT NOT NULL,
        billing_email VARCHAR(255) NOT NULL UNIQUE,
        billing_address TEXT,
        phone VARCHAR(50),
        website TEXT,
        subscription_id UUID NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        CONSTRAINT fk_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}


try {
    // Step 2: Create subscriptions table
    $tableSql = <<<SQL
    CREATE TABLE roles (
        id UUID PRIMARY KEY,
        role_name TEXT NOT NULL UNIQUE
    );

    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
try {
    // Step 2: Create subscriptions table
    $tableSql = <<<SQL
    CREATE TYPE payment_status_enum AS ENUM ('active', 'pending', 'failed', 'canceled');

    CREATE TABLE subscriptions (
        id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        organization_id UUID NOT NULL,
        tier_id UUID NOT NULL,
        start_date DATE NOT NULL,
        renewal_date DATE NOT NULL,
        payment_status payment_status_enum NOT NULL,
        payment_method_id TEXT,
        auto_renew BOOLEAN DEFAULT FALSE,
        price DECIMAL(10, 2) NOT NULL,
        currency CHAR(3),
        features_enabled JSONB,
        extra_seats INT DEFAULT 0,
        extra_seats_price DECIMAL(10, 2) DEFAULT 0.00,
        deleted_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT fk_organization FOREIGN KEY (organization_id) REFERENCES organizations(id),
        CONSTRAINT fk_tier FOREIGN KEY (tier_id) REFERENCES tiers(id)
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}

try {
    // Step 2: Create subscriptions table
    $tableSql = <<<SQL
    CREATE TABLE tiers (
        id UUID PRIMARY KEY,
        tier_name NOT NULL UNIQUE,
        max_users INT(5),
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