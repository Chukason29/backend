<?php
$config = require __DIR__ . '/../db.php';
ob_end_clean();
// Set CORS headers for both preflight and actual requests
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

//require __DIR__ . '/vendor/autoload.php';
// Handle preflight request and stop here
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    file_put_contents('log.txt', 'OPTIONS request received'.PHP_EOL, FILE_APPEND);
    http_response_code(200);
    //exit();
}

// Step 1: Fetch JSON from external API
$json = file_get_contents('php://input');

// Step 2: Decode the JSON into an associative array
$data = json_decode($json, true);
$username = $data['name'];
$email = $data['email'];
$password = 'secret123'; // plaintext, to be hashed
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Step 3: Create a new array with only name and email
try {
    // SQL: Create users table if it doesn't exist
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS warehouse_users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    SQL;

    $pdo->exec($sql);
    $stmt = $pdo->prepare("
        INSERT INTO warehouse_users (username, email, password_hash)
        VALUES (:username, :email, :password_hash)
    ");

    // Bind values safely
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $passwordHash);

    if ($stmt->execute()) {
        $result = [
            'status' => "success",
            'message' => "my name is $username and my email is $email",
        ];
    } else {
        $result = [
            'status' => "error",
            'message' => "⚠️ Failed to insert user."
        ];
    }

} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}

// Step 4: Encode it back to JSON and print
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?>
