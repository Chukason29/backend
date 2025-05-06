<?php

require __DIR__ . '/../db.php';
require_once "./functions.php";

ob_end_clean();

$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Set CORS headers
header("Access-Control-Allow-Origin: http://127.0.0.1:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
//file_put_contents('log.txt', "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . PHP_EOL, FILE_APPEND);


// Handle OPTIONS preflight
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// JSON body
$data = json_decode(file_get_contents('php://input'), true);

// Respond helper
function respond($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

try {
    if ($method === 'POST' && $uri === '/api/register') {
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
    }

    // Fallback
    respond(['error' => 'Route not found'], 404);

} catch (PDOException $e) {
    respond(['error' => $e->getMessage()], 500);
}
