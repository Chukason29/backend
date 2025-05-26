<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$isDev = ($config['environment']['environment'] === 'development');
$refreshToken = null;

if ($isDev) {
    // Dev: read from JSON body or Authorization header
    $input = json_decode(file_get_contents("php://input"), true);
    $refreshToken = $input['refresh_token'] ?? null;

    if (!$refreshToken && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if (str_starts_with($authHeader, 'Bearer ')) {
            $refreshToken = substr($authHeader, 7);
        }
    }
} else {
    // Prod: read from HttpOnly cookie
    $refreshToken = $_COOKIE['refresh_token'] ?? null;
}

if (!$refreshToken) {
    respond(['status' => 'error', 'message' => 'No refresh token provided'], 401);
}

// 2️⃣ Check database
try {
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM refresh_tokens WHERE token = ?");
    $stmt->execute([$refreshToken]);
    $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tokenRow || strtotime($tokenRow['expires_at']) < time()) {
        respond(['status' => 'error', 'message' => 'Invalid or expired refresh token'], 401);
    }

    // 3️⃣ Issue new access token
    $jwtSecret = $_ENV['JWT_SECRET'];
    $newAccessToken = generateAccessToken($tokenRow['user_id'], $jwtSecret);

    respond([
        'status' => 'success',
        'access_token' => $newAccessToken
    ]);
} catch (PDOException $e) {
    respond(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
}
