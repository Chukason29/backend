<?php
## tokenization.php is used for
#checking if access token is valid or expired
#decrypting the user id from the token
# checking if user has access to the requested resource
session_start();
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
$token = generateTimedToken($email, 172800); //expires in 48hours after creation
$verifyLink = $config['url']['BASE_URL'].'/auth/update_password?token='.$token;
$organization_name = $_SESSION["organization_name"];


$stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = :id");
$stmt->bindValue(':id', $organization_id);
$stmt->execute();
$organization = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$role) {
    respond(["status" => "error", 'message' => 'Organization not found'], 500);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt1 = $pdo->prepare("INSERT INTO users ( name, email, password_hash, role_id) VALUES (:name, :email, :hashed_password, :role_id)");
    $stmt1->execute([
        ':name' => $name,
        ':email' => $email,
        ':hashed_password' => $hashedPassword, // Always hash passwords
        ':role_id' => $role_id
    ]);
    #TODO ==> input the token to the token table with false as the check column
    $stmt2 = $pdo->prepare("INSERT INTO link_token (email, token) VALUES (:email, :token)");
    $stmt2->execute([
        ':email' => $email,
        ':token' => $token
    ]);
    if ($pdo->commit() && addUserEmail($email, $name, $organization_name, $password, $verifyLink, dirname(__DIR__, 2)."/templates/email_verification.html", $config['mail']['password'])) {
        respond(["status" => "success", "message" => "Account Succesfully Created", "password" => $password], 200);
        exit;
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    respond(["status" => "error", 'message' => 'Database error: ' . $e->getMessage()], 500);
}
