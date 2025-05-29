<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

$access_token = getBearerToken();
if (!$access_token) {
    respond(['status' => 'error', 'message' => 'Access token is required'], 401);
}