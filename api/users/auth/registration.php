<?php

if (!isset($data['name'], $data['email'], $data['password'])) {
    respond(["status" => "false",'message' => 'All fields are required'], 400);
}

#TODO check if email is valid
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    respond(["status" => "false", 'message' => 'Invalid email address'], 400);
}
if (empty($data["name"])) {
    respond(["status" => "false", 'message' => 'Name is required'], 400);
}

if (empty($data['password'])) {
    respond(["status" => "false", 'message' => 'Password is required'], 400);
}

if (strlen($data['password']) < 8) {
    respond(["status" => "false", 'message' => 'Password must be at least 8 characters long'], 400);
}

$name = strtolower(sanitizeInput($data["name"]));
$email = strtolower(sanitizeInput($data["email"]));

$hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

if (emailExists($pdo, $email)) {
    respond(["status" => "false", 'message' => 'Account already exists'], 400);
}
#TODO ==> Create a timed token based on the user's email and attach to the base url
$token = generateTimedToken($email, 86400); //expires in 24hours after creation

respond(["message" => 'Token generated successfully', 'token' => $token], 200);

$pdo->beginTransaction();
    $stmt1 = $pdo->prepare("INSERT INTO users ( name, email, hashed_password) VALUES (:name, :email, :hashed_password)");
    $stmt1->execute([
        ':name' => $name,
        ':email' => $email,
        ':hashed_password' => $hashedPassword, // Always hash passwords
        
    ]);

    #TODO ==> input the token to the token table with false as the check column
    $stmt2 = $pdo->prepare("INSERT INTO link_token (email, token) VALUES (:email, :token)");
    $stmt2->execute([
        ':email' => $email,
        ':token' => $token
    ]);

respond(['message' => 'User registered successfully'], 201);