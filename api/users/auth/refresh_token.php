<?php
session_start();

# get the refresh token from the cookie
$refreshToken = $_COOKIE['refresh_token'] ?? null;

if (!$refreshToken) {
    respond(['status' => 'error', 'message' => 'No refresh token provided'], 401);
}

// 2️⃣ Check database to check if the refresh token is valid
try {
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM refresh_tokens WHERE token = ?");
    $stmt->execute([$refreshToken]);
    $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tokenRow || strtotime($tokenRow['expires_at']) < time()) {
        respond([
            'status' => 'error',
            'message' => 'Invalid or expired refresh token', 
            "redirect_url" => $config['url']['BASE_URL'] . 'auth/login?error=invalid_token'
        ], 401);
    }   
    // 3️⃣ Issue new access token
    $jwtSecret = $config['secret']['SECRET_KEY'];
    $encryptionKey = $config['secret']['ENCRYPTION_KEY'];

    #encrypt the user id
    $encrypted_user_id = encryptUserId($tokenRow['user_id'], $encryptionKey);

    #generate access token
    $newAccessToken = generateAccessToken($encrypted_user_id, $_SESSION['role_name'], $_SESSION['email'], $email, $organization_id, $secret, $expiresIn = 3600 * 12);
    #getting user details           

    respond([
        'status' => 'success',
        'access_token' => $newAccessToken
    ]);
} catch (PDOException $e) {
    respond(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    respond(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()], 401);
} catch (\Throwable $th) {
    respond(['status' => 'error', 'message' => 'Error: ' . $th->getMessage()], 500);
}
