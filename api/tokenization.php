<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;



$jwt_secret = $config['secret']['SECRET_KEY'];
$access_token = getBearerToken();
if (!$access_token) {
    respond(['status' => 'error', 'message' => 'Access token is required'], 401);
}
$decoded_token = decodeAccessToken($access_token, $jwt_secret);
if (!$decoded_token) {
    respond(['status' => 'error', 'message' => 'Invalid access token'], 401);
}
if (isset($decoded_token->message) && $decoded_token->message === 'Token expired') {
    require_once __DIR__ . '/refresh_token.php';
    exit;
}

$user_id = decryptUserId($decoded_token->sub, $encryptionKey);
$email = $decoded_token->user->email;
$organization_id = $decoded_token->user->organization_id ?? null;
$role_name = $decoded_token->user->role_name;


#Check if the user has access to the requested resource
#This function is from permissions.php
respond(['status' => 'success', 'message' => $uri], 200);
 if (!hasAccess($role_name, $uri, $roles)) {
    respond(['status' => 'error', 'message' => 'Unauthorized access'], 403);
 }
