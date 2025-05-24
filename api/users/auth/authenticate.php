<?php
// Include JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//Checking if the user is logged in
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    respond(['status' => 'error', 'message' => 'Unauthorized access'], 401);
}

// JWT config
$jwt_secret = $config['secret']['SECRET_KEY'];  
$accessToken = generateAccessToken($user_id, $jwt_secret);
$refreshToken = generateRefreshToken();
$refreshExpiry = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // 30 days

$stmt = $pdo->prepare("INSERT INTO refresh_tokens (token, user_id, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$refreshToken, $_SESSION['user_id'], $refreshExpiry]);

$_SESSION['jwt'] = $jwt;

// Return response
respond(
    [
        'status' => 'success',
        'message' => 'Login successful',
        'access_token' => $accessToken,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'is_active' => $user['is_active']
        ]
    ],
    200
);