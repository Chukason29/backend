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
$encryptionKey = $config['secret']['ENCRYPTION_KEY'];
$encrypted_user_id = encryptUserId($user_id, $encryptionKey);

#generate access token
$accessToken = generateAccessToken($encrypted_user_id, $_SESSION['role_name'], $_SESSION['name'], $_SESSION['email'], $_SESSION['organization_id'], $jwt_secret);

#generate refresh token
$refreshToken = generateRefreshToken();
$refreshExpiry = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // 30 days

$isDev = ($config['environment']['environment'] === 'development');

#Checking if the user is not in development mode
if (!$isDev) {
    setcookie(
        'refresh_token',
        $refreshToken,
        [
            'expires' => time() + 60 * 60 * 24 * 30, // 30 days
            'domain' => "basefood.trendsaf.co",
            'path' => '/',
            'httponly' => true,
            'secure' => true, // Must be HTTPS
            'samesite' => 'None' // Allows cross-site requests
        ]
    );
}


try {
    $check = $pdo->prepare("SELECT * FROM refresh_tokens WHERE user_id = ?");
    $check->execute([$_SESSION['user_id']]);
    $existingToken = $check->fetch(PDO::FETCH_ASSOC);

    $pdo->beginTransaction();
    if (!$existingToken) {
        $stmt = $pdo->prepare("INSERT INTO refresh_tokens (token, user_id, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$refreshToken, $_SESSION['user_id'], $refreshExpiry]);
    } else {
        $stmt = $pdo->prepare("UPDATE refresh_tokens SET token = ?, expires_at = ? WHERE user_id = ?");
        $stmt->execute([$refreshToken, $refreshExpiry, $_SESSION['user_id']]);
    }
    
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    respond(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    respond(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()], 401);
}


$_SESSION['access_token'] = $accessToken;

#preparing the response
$response = [
    'status' => 'success',
    'message' => 'Login successful',
    'access_token' => $accessToken
];

// If in development mode, include the refresh token in the response
if ($isDev) {
    $response['refresh_token'] = $refreshToken;
}
// Return response
respond($response, 200);