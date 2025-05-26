<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: http://localhost:3001");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$refreshToken = $_COOKIE['refresh_token'] ?? null;
if (!$refreshToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Refresh token missing']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM refresh_tokens WHERE token = ? AND is_used = FALSE");
    $stmt->execute([$refreshToken]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || strtotime($row['expires_at']) < time()) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired refresh token']);
        exit;
    }

    $userId = $row['user_id'];
    $accessToken = generateAccessToken($userId, $_ENV['SECRET_KEY']);

    echo json_encode([
        'access_token' => $accessToken,
        'user_id' => $userId
    ]);
} catch (PDOException $e){
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
