<?php
$config = require __DIR__ . '/../db.php';
ob_end_clean();
$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
// Set CORS headers for both preflight and actual requests
header("Access-Control-Allow-Origin: http://127.0.0.1:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require dirname(__DIR__) . '/vendor/autoload.php';

use Ramsey\Uuid\Uuid;

// Handle preflight request and stop here
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    file_put_contents('log.txt', 'OPTIONS request received'.PHP_EOL, FILE_APPEND);
    http_response_code(200);
    //exit();
}

// Step 1: Fetch JSON from external API
$json = file_get_contents('php://input');
$data = json_decode(file_get_contents('php://input'), true);


try {
    
    if ($method === 'POST' && $uri === '/register') {
        if (!isset($data['name'], $data['email'], $data['password'])) {
            respond(['error' => 'All fields are required'], 400);
        }
    
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            respond(['error' => 'Invalid email address'], 400);
        }
    
        // Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            respond(['error' => 'Email already registered'], 409);
        }
    
        // Insert into DB
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'], $hashedPassword]);
    
        respond(['message' => 'User registered successfully'], 201);
    }

} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}

// Step 4: Encode it back to JSON and print
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?>
