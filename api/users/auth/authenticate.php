<?php
// Include JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// JWT config
$jwt_secret = $config['secret']['SECRET_KEY'];  
$jwt_payload = [
    'iat' => time(), // Issued at
    'iss' => 'https://basefood.trendsaf.co', // Issuer
    'name' => $_SESSION['name'],
    'role_name' => $_SESSION['role_name'],
    'user_id' => $_SESSION['user_id'],
    'organization_id' => $_SESSION['organization_id'],
    'email' => $_SESSION['email'],
    'exp' => time() + (60 * 60 * 48) // 48 hours
];

// Create JWT
$jwt = JWT::encode($jwt_payload, $jwt_secret, 'HS256');

// Store in session (if needed)
setcookie('token', $jwt, [
    'expires' => time() + 604800,
    'path' => '/',
    'domain' => '', // optional: use your domain
    'secure' => true,     // true if using HTTPS
    'httponly' => true,   // prevent JS access
    'samesite' => 'Lax'   // or 'Strict' / 'None' if cross-site
]);
$_SESSION['jwt'] = $jwt;

// Return response

