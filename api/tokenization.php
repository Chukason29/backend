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
if (is_array($decoded_token) && isset($decoded_token['code']) && $decoded_token['code'] === 401) {
    respond(['status' => 'error', 'message' => $decoded_token['message']], 401);    
    # code...
}