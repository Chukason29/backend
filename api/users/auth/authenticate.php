<?php
// Include JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// JWT config
$jwt_secret = $config['secret']['SECRET_KEY'];  
$accessToken = generateAccessToken($userId, $jwt_secret);
$refreshToken = generateRefreshToken();
$refreshExpiry = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // 30 days

$stmt = $pdo->prepare("INSERT INTO refresh_tokens (token, user_id, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$refreshToken, $userId, $refreshExpiry]);

// Store in session (if needed)
setcookie('refresh_token', $refreshToken, [
    'expires' => time() + 60 * 60 * 24 * 30,
    'path' => '/',
    'domain' => '', // optional: use your domain
    'secure' => true,     // true if using HTTPS
    'httponly' => true,   // prevent JS access
    'samesite' => 'strict'   // or 'Strict' / 'None' if cross-site
]);
$_SESSION['jwt'] = $jwt;

// Return response

