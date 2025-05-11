<?php
    session_start();
    #TODO
    // JSON body
    use Ramsey\Uuid\Uuid;
    $data = json_decode(file_get_contents('php://input'), true);

    // Respond helper
    function respond($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    #TODO ==> Make sure email and password is inputted
    if (!$_SESSION['user_id']) {
        respond(["status" => "error", 'message' => "unauthorized access"], 400);
        exit;
    }
    if (!isset($data['name'], $data['billing_email'], $data['billing_address'], $data['phone'], $data['website'])) {
        respond(["status" => "false",'message' => 'All fields are required'], 400);
        exit;
    }
    $name = strtolower(sanitizeInput($data["name"]));
    $billing_email = strtolower(sanitizeInput($data["billing_email"]));
    $billing_address = strtolower(sanitizeInput($data["billing_address"]));
    $phone_number = strtolower(sanitizeInput($data["phone"]));
    $website = strtolower(sanitizeInput($data["website"]));
    $user_id = $_SESSION['user_id'];

    $organization_id = Uuid::uuid4()->toString();
    $subscription_id = Uuid::uuid4()->toString();

    $stmt = $pdo->prepare("SELECT * FROM tiers WHERE tier_name = :tier_name");
    $stmt->bindValue(':tier_name', 'Free');
    $stmt->execute();
    $tier = $stmt->fetch(PDO::FETCH_ASSOC);
    $tier_id = $tier['id'];
    respond($tier);
