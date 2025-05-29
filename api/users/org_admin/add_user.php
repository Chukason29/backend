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
respond(['status' => 'success', "payload" => $decoded_token], 200);


$refresh_token = $_COOKIE['refresh_token'] ?? null;
//respond(['status' => 'success', 'token' => $access_token], 200);
