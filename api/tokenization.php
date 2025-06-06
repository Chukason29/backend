<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;



$jwt_secret = $config['secret']['SECRET_KEY'];
$encryptionKey = $config['secret']['ENCRYPTION_KEY'];
$access_token = getBearerToken();
if (!$access_token) {
    respond(['status' => 'error', 'message' => 'Access token is required'], 401);
}

#decode the access token
$decoded_token = decodeAccessToken($access_token, $jwt_secret);
if (!$decoded_token) {
    respond(['status' => 'error', 'message' => 'Invalid access token'], 401);
}
#check if decoded token is an array and has a message property
if (is_array($decoded_token) && isset($decoded_token["message"])) {
    if($decoded_token["message"] === 'Expired token'){ #checking of the token is expired
        require_once __DIR__ . '/refresh_token.php'; #refresh the token
        exit;
    }
}

#decrypt the user id from the token
$user_id = decryptUserId($decoded_token->sub, $encryptionKey);

#get the user details from the access token
$email = $decoded_token->user->email;
$organization_id = $decoded_token->user->organization_id ?? null;
$role = $decoded_token->user->role;


#Check if the user has access to the requested resource
#This function is from permissions.php
 if (!hasAccess($role, $uri, $roles)) {
    respond(['status' => 'error', 'message' => 'Unauthorized access'], 403);
 }
