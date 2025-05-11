<?php


use Ramsey\Uuid\Uuid;
$data = json_decode(file_get_contents('php://input'), true);

// Respond helper
function respond($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
$uuid = Uuid::uuid4()->toString();

if (!isset($data['tier_name'], $data['price'], $data['max_users'])) {
    respond(["status" => "false",'message' => 'All fields are required'], 400);
}
try {
    $pdo->beginTransaction();
    $stmt1 = $pdo->prepare("INSERT INTO tiers ( tier_id, tier_name, price, max_users ) VALUES (:tier_id, :tier_name, :price, :max_users)");
    $stmt1->execute([
        ':tier_id' => $uuid,
        ':tier_name' => $data['tier_name'],
        ':price' => $data['price'],
        ':max_users' => $data['max_users']
    ]);

     #TODO commit data to database and send link to email address
    if ($pdo->commit() ){
        respond(["status" => "success", "message" => "tier added successfully"], 200);
        exit;
    }else{
        respond(["status" => "error", "message" => "Unsuccessful"], 200);   
    }

respond(['message' => 'User registered successfully'], 201);
} catch (PDOException $e) {
    $pdo->rollBack();
    respond(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    $pdo->rollBack();
    respond(['error' => 'Error: ' . $e->getMessage()], 500);
}

