<?php
// config.php

require __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    (Dotenv\Dotenv::createImmutable(__DIR__))->load(); // using load() for strict behavior
}

session_set_cookie_params([
    'lifetime' => 3600, // Expires on browser close (or use custom time)
    'path' => '/',
    'domain' => 'basefood.trendsaf.co', // Set your domain explicitly
    'secure' => False,  // Ensures cookie only sent over HTTPS
    'httponly' => False,     // JS can't access the cookie
    'samesite' => 'None',    // Helps prevent CSRF (use 'Strict' for tighter security)
]);

ini_set('session.use_strict_mode', 0);        // Reject uninitialized session IDs
ini_set('session.use_only_cookies', 1);       // Prevent session IDs in URLs
ini_set('session.cookie_secure', 0);          // Force HTTPS
ini_set('session.cookie_httponly', 0);        // Prevent JS access

return [
    //
    // APPLICATION SETTINGS
    //
    'app' => [
        'name'      => $_ENV['APP_NAME']      ?? null,
        'env'       => $_ENV['APP_ENV']       ?? null,
        'debug'     => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url'       => $_ENV['APP_URL']       ?? null,
        'port'      => (int)($_ENV['APP_PORT'] ?? 80),
        'timezone'  => $_ENV['APP_TIMEZONE']  ?? 'UTC',
    ],

    //
    // DATABASE (PDO)
    //
    'db' => [
        'driver'    => $_ENV['DB_DRIVER']   ?? null,
        'host'      => $_ENV['DB_HOST']     ?? null,
        'port'      => $_ENV['DB_PORT']     ?? null,
        'database'  => $_ENV['DB_DATABASE'] ?? null,
        'schema'    => $_ENV['DB_SCHEMA']   ?? null,
        'username'  => $_ENV['DB_USERNAME'] ?? null,
        'password'  => $_ENV['DB_PASSWORD'] ?? null,
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ],

    //
    // SWAGGER / OpenAPI
    //
    'swagger' => [
        'spec_path' => __DIR__.'/../docs/swagger.yaml',
        'ui_url'    => $_ENV['SWAGGER_UI_URL'] ?? '/swagger-ui',
    ],

    //
    // JWT AUTH
    //
    'jwt' => [
        'secret'     => $_ENV['JWT_SECRET']     ?? null,
        'algo'       => $_ENV['JWT_ALGO']       ?? null,
        'issuer'     => $_ENV['JWT_ISSUER']     ?? null,
        'audience'   => $_ENV['JWT_AUDIENCE']   ?? null,
        'expires_in' => (int)($_ENV['JWT_EXPIRES_IN'] ?? null),
    ],

    'secret' => [
        'SECRET_KEY' => $_ENV['SECRET_KEY']     ?? null,
        'ENCRYPTION_KEY' => $_ENV['ENCRYPTION_KEY'] ?? null,
        'ENCRYPTION_ALGO' => $_ENV['ENCRYPTION_ALGO'] ?? 'aes-256-cbc'
    ],

    //
    // CORS
    //
    'cors' => [
        'allow_origins'        => explode(',', $_ENV['CORS_ALLOW_ORIGINS'] ?? '*'),
        'allow_methods'        => explode(',', $_ENV['CORS_ALLOW_METHODS'] ?? 'GET,POST,PUT,DELETE,OPTIONS'),
        'allow_headers'        => explode(',', $_ENV['CORS_ALLOW_HEADERS'] ?? 'Content-Type,Authorization'),
        'expose_headers'       => explode(',', $_ENV['CORS_EXPOSE_HEADERS'] ?? ''),
        'max_age'              => (int)($_ENV['CORS_MAX_AGE'] ?? 0),
        'supports_credentials' => filter_var($_ENV['CORS_CREDENTIALS'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ],

    //
    // CACHE
    //
    'cache' => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'path'   => __DIR__.'/../cache',
        'ttl'    => (int)($_ENV['CACHE_TTL'] ?? 3600),
    ],

    //
    // SESSION
    //
    'session' => [
        'name'     => $_ENV['SESSION_NAME']     ?? 'app_session',
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
        'path'     => '/',
        'domain'   => $_ENV['SESSION_DOMAIN']   ?? null,
        'secure'   => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'httponly' => true,
        'samesite' => $_ENV['SESSION_SAMESITE'] ?? 'Lax',
    ],

    //
    // FILE UPLOADS
    //
    'upload' => [
        'max_size'      => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5 * 1024 * 1024),
        'allowed_types' => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,pdf,docx'),
        'path'          => __DIR__.'/../uploads',
    ],

    //
    // MAIL
    //
    'mail' => [
        'host'       => $_ENV['MAIL_HOST']       ?? null,
        'port'       => (int)($_ENV['MAIL_PORT']) ?? 587,
        'username'   => $_ENV['MAIL_USERNAME']   ?? null,
        'password'   => $_ENV['MAIL_PASSWORD']   ?? null,
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from' => [
            'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? null,
            'name'    => $_ENV['MAIL_FROM_NAME']    ?? 'Trendsaf BaseFood',
        ],
    ],
    'url' => [
        'BASE_URL' => $_ENV['BASE_URL'] ?? null,
        'LIVE_URL' => $_ENV['LIVE_URL'] ?? null,
    ],
    'roles' => [
        'ORGANIZATION_ADMIN' => $_ENV['ORGANIZATION_ADMIN'] ?? null,
    ],
    'environment' => [
        'environment' => $_ENV['ENVIRONMENT'] ?? null,
    ],
];
