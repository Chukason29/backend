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
}elseif ($decoded_token["code"] === 401) { // Check if the token is expired
    respond(['status' => 'error', 'message' => 'Expired Access Token'], 401);
    exit;
    # code...
}