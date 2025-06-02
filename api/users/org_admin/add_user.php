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
$password = bin2hex(random_bytes(4)); // 16 characters long

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
if (emailExists($pdo, $data['email'])) {
    respond(["status" => "error", 'message' => 'Account already exists'], 400);
}
$name = strtolower(sanitizeInput($data["name"]));
$email = strtolower(sanitizeInput($data["email"]));

try {
    $pdo->beginTransaction();
    $stmt1 = $pdo->prepare("INSERT INTO users ( name, email, password_hash, role_id) VALUES (:name, :email, :hashed_password, :role_id)");
    $stmt1->execute([
        ':name' => $name,
        ':email' => $email,
        ':hashed_password' => $hashedPassword, // Always hash passwords
        ':role_id' => $role_id
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    respond(["status" => "error", 'message' => 'Database error: ' . $e->getMessage()], 500);
}
