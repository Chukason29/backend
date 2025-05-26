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

$isDev = ($config['environment']['environment'] === 'development');

#Checking if the user is not in development mode
if (!$isDev) {
    setcookie(
        'refresh_token',
        $refreshToken,
        [
            'expires' => $refreshExpiry,
            'path' => '/',
            'httponly' => true,
            'secure' => true, // Must be HTTPS
            'samesite' => 'Lax'
        ]
    );
}


try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO refresh_tokens (token, user_id, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$refreshToken, $_SESSION['user_id'], $refreshExpiry]);
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    respond(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    respond(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()], 401);
}


$_SESSION['$accessToken'] = $accessToken;

#preparing the response
$response = [
    'status' => 'success',
    'message' => 'Login successful',
    'access_token' => $accessToken,
    'user' => [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['name'],
        'email' => $_SESSION['email'],
        'role_id' => $_SESSION['role_id'],
        'role_name' => $_SESSION['role_name']
    ]
];

// If in development mode, include the refresh token in the response
if ($isDev) {
    $response['refresh_token'] = $refreshToken;
}
// Return response
respond($response, 200);