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
        respond([
            'status' => 'error',
            'message' => 'Invalid or expired refresh token', 
            "redirect_url" => $config['url']['BASE_URL'] . '/login?error=invalid_token']
            , 401);
    }

    // 3️⃣ Issue new access token
    $jwtSecret = $config['secret']['SECRET_KEY'];
    $newAccessToken = generateAccessToken($tokenRow['user_id'], $jwtSecret);

    #getting user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$tokenRow['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        respond(['status' => 'error', 'message' => 'User not found'], 404);
    }

    #Getting the role name from the roles table for the user
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :role_id");
    $stmt->bindValue(':role_id', $user['role_id']);
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$role) {
        respond(["status" => "error", 'message' => 'Role not found'], 500);
        exit;
    }

    respond([
        'status' => 'success',
        'access_token' => $newAccessToken,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'role_name' => $role['role_name'] ?? null,
            'organization_id' => $user['organization_id'] ?? null
        ]
    ]);
} catch (PDOException $e) {
    respond(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
}
