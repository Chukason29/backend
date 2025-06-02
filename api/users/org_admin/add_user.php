<?php
## tokenization.php is used for
#checking if access token is valid or expired
#decrypting the user id from the token
# checking if user has access to the requested resource
require __DIR__ . '/../../tokenization.php'; 
    
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name'], $data['email'], $data['role_id'])) {
    respond(["status" => "error",'message' => 'All fields are required'], 400);
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    respond(["status" => "error", 'message' => 'Invalid email address'], 400);
}
if (empty($data["name"])) {
    respond(["status" => "error", 'message' => 'Name is required'], 400);
}
#generate a random password 
$password = bin2hex(random_bytes(4 )); // 16 characters long

respond(["status" => "success", 'message' => 'User password generated successfully', 'password' => $password], 200);