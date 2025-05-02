<?php
$config = require __DIR__ . '/../db.php';
ob_end_clean();
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

// Step 2: Decode the JSON into an associative array
$data = json_decode($json, true);
$name = $data['name'];
//$email = $data['email'];
$code = $data['code'];
$location = $data['location'];
$country = $data['country'];
$address = $data['address'];
$capacity = $data['capacity'];
$capacity_unit = $data['capacity_unit'];
$utilization = $data['utilization'];
$active = FALSE;
$password = 'secret123'; // plaintext, to be hashed
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$user_uuid = Uuid::uuid4();

// Step 3: Create a new array with only name and email
try {
    
    $stmt = $pdo->prepare("
        INSERT INTO warehouses (id, name, code, location, country, address, capacity, 
        capacity_unit, utilization, active)
        VALUES (:id, :name, :code, :location, :country, :address, :capacity, 
        :capacity_unit, :utilization, :active)
    ");

    // Bind values safely
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':id', $user_uuid);
    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':capacity', $capacity);
    $stmt->bindParam(':capacity_unit', $capacity_unit);
    $stmt->bindParam(':utilization', $utilization);
    $stmt->bindParam(':active', $active);


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
