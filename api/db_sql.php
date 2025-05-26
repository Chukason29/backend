<?php
$config = require __DIR__ . '/../db.php';


try {
    // TIERS table
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS tiers (
        id UUID PRIMARY KEY,
        tier_name TEXT NOT NULL UNIQUE,
        price DECIMAL(10, 2),
        max_users INT
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Tiers table error: " . $e->getMessage();
    exit;
}

try {
    // ROLES table
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS roles (
        id UUID PRIMARY KEY,
        role_name TEXT NOT NULL UNIQUE
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Roles table error: " . $e->getMessage();
    exit;
}
try {
    // Define ENUM type only once
    $pdo->exec("DO \$\$ BEGIN
        IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'payment_status_enum') THEN
            CREATE TYPE payment_status_enum AS ENUM ('active', 'pending', 'failed', 'canceled');
        END IF;
    END \$\$;");

    // SUBSCRIPTIONS table
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS subscriptions (
        id UUID PRIMARY KEY,
        tier_id UUID,
        start_date DATE,
        renewal_date DATE,
        payment_status payment_status_enum,
        payment_method_id TEXT,
        auto_renew BOOLEAN DEFAULT FALSE,
        price DECIMAL(10, 2),
        currency CHAR(3),
        features_enabled JSONB,
        deleted_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_tier FOREIGN KEY (tier_id) REFERENCES tiers(id)
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Subscriptions table error: " . $e->getMessage();
    exit;
}
try {
    // ORGANIZATIONS table
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS organizations (
        id UUID PRIMARY KEY,
        name TEXT NOT NULL,
        billing_email VARCHAR(255) UNIQUE,
        billing_address TEXT,
        phone VARCHAR(50),
        website TEXT,
        subscription_id UUID,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        CONSTRAINT fk_subscription_organization FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Organizations table error: " . $e->getMessage();
    exit;
}

try {
    // USERS table
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS users (
        id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        email VARCHAR(255) NOT NULL UNIQUE,
        name TEXT,
        password_hash TEXT,
        organization_id UUID ,
        role_id UUID ,
        last_login TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        profile_picture JSONB,
        position TEXT,
        deleted_at TIMESTAMP,
        is_active BOOLEAN DEFAULT FALSE,
        is_deleted BOOLEAN DEFAULT FALSE,
        CONSTRAINT fk_organization FOREIGN KEY (organization_id) REFERENCES organizations(id),
        CONSTRAINT fk_role FOREIGN KEY (role_id) REFERENCES roles(id)
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Users table error: " . $e->getMessage();
    exit;
}

try {
    // LINK TOKEN table
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS link_token (
        token TEXT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        is_used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Link token table error: " . $e->getMessage();
    exit;
}

try {
    $tableSql = <<<SQL
    CREATE TABLE IF NOT EXISTS refresh_tokens (
        token TEXT PRIMARY KEY,
        user_id UUID REFERENCES users(id),
        is_used BOOLEAN DEFAULT FALSE,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    SQL;
    $pdo->exec($tableSql);
} catch (PDOException $e) {
    echo "❌ Tiers table error: " . $e->getMessage();
    exit;
}
