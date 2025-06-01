<?php
session_start();

// 1️⃣ Get refresh token (cookie or dev header/body)
$isDev = ($_ENV['APP_ENV'] === 'development');
$refreshToken = null;
// Prod: Get from secure cookie
$refreshToken = $_COOKIE['refresh_token'] ?? null;


// 2️⃣ Delete refresh token from database if it exists
if ($refreshToken) {
    try {
        $stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE token = ?");
        $stmt->execute([$refreshToken]);
    } catch (PDOException $e) {
        // Log error (don't block logout on DB failure)
    }
}

// 3️⃣ Clear session
$_SESSION = [];
session_unset();
session_destroy();

// 4️⃣ Expire cookie
setcookie('refresh_token', '', time() - 3600, '/', '', true, true);

// 5️⃣ Respond
respond(['status' => 'success', 'message' => 'Logged out successfully'], 200);
