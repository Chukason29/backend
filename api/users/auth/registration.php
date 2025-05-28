<?php

// JSON body
$data = json_decode(file_get_contents('php://input'), true);

// Respond helper

if (!isset($data['name'], $data['email'], $data['password'])) {
    respond(["status" => "error",'message' => 'All fields are required'], 400);
}

#TODO check if email is valid
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    respond(["status" => "error", 'message' => 'Invalid email address'], 400);
}
if (empty($data["name"])) {
    respond(["status" => "error", 'message' => 'Name is required'], 400);
}

if (empty($data['password'])) {
    respond(["status" => "error", 'message' => 'Password is required'], 400);
}

if (strlen($data['password']) < 8) {
    respond(["status" => "error", 'message' => 'Password must be at least 8 characters long'], 400);
}

$name = strtolower(sanitizeInput($data["name"]));
$email = strtolower(sanitizeInput($data["email"]));

$hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

if (emailExists($pdo, $email)) {
    respond(["status" => "error", 'message' => 'Account already exists'], 400);
}
#TODO ==> Create a timed token based on the user's email and attach to the base url
$token = generateTimedToken($email, 172800); //expires in 48hours after creation
$verifyLink = $config['url']['BASE_URL'].'/auth/verify?token='.$token;
$stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?"); // Fast check
$stmt->execute([$config['roles']['ORGANIZATION_ADMIN']]);
$role = $stmt->$roles = $stmt->fetch(PDO::FETCH_ASSOC);

$role_id = $role['id'];



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



     #TODO commit data to database and send link to email address
    if ($pdo->commit() && sendHTMLEmail($email, $name, $verifyLink, dirname(__DIR__, 2)."/templates/email_verification.html", $config['mail']['password'])) {
        respond(["status" => "success", "message" => "Registration successful, link sent to your email"], 200);
        exit;
    }else{
        respond(["status" => "error", "message" => "Registration is unsuccessful"], 200);   
    }

respond(['message' => 'User registered successfully'], 201);
} catch (PDOException $e) {
    $pdo->rollBack();
    respond(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    $pdo->rollBack();
    respond(['error' => 'Error: ' . $e->getMessage()], 500);
}
    