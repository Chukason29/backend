<?php
// Include JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// JWT config
$jwt_secret = $config['secret']['SECRET_KEY'];  
$jwt_payload = [
    'iat' => time(), // Issued at
    'iss' => 'https://warehouse.trendsaf.co', // Issuer

    'user_id' => $user_id,
    'organization_id' => $organization_id,
    'email' => $_SESSION['email'],
    'exp' => time() + (60 * 60 * 48) // 48 hours
];

// Create JWT
$jwt = JWT::encode($jwt_payload, $jwt_secret, 'HS256');

// Store in session (if needed)
$_SESSION['jwt'] = $jwt;

// Return response
respond([
    'status' => 'success',
    'message' => 'Organization created and user authenticated',
    'token' => $jwt,
    'session' => session_id()
], 200);
