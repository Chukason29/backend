<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

//require __DIR__ . '/vendor/autoload.php';
// Handle preflight request and stop here
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    file_put_contents('log.txt', 'OPTIONS request received'.PHP_EOL, FILE_APPEND);
    http_response_code(200);
    exit();
}