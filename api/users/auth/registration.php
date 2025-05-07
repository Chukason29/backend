<?php

if (!isset($data['name'], $data['email'], $data['password'])) {
    respond(["status" => "false",'message' => 'All fields are required'], 400);
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    respond(["status" => "false", 'message' => 'Invalid email address'], 400);
}
if (empty($data["name"])) {
    respond(["status" => "false", 'message' => 'Name is required'], 400);
}
$name = strtolower(sanitizeInput($data["name"]));
$email = strtolower(sanitizeInput($data["email"]));

$hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

if (emailExists($pdo, $email)) {
    respond(["status" => "false", 'message' => 'Account already exists'], 400);
}


$stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
$stmt->execute([$data['name'], $data['email'], $hashedPassword]);

respond(['message' => 'User registered successfully'], 201);