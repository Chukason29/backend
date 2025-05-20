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

    $tier_name = 'free';
    #TODO ==> Query tiers table to get the tier id
    $stmt = $pdo->prepare("SELECT * FROM tiers WHERE tier_name = :tier_name");
    $stmt->bindValue(':tier_name', $tier_name);
    $stmt->execute();
    $tier = $stmt->fetch(PDO::FETCH_ASSOC);
    $tier_id = $tier['tier_id'];

    try {
        $pdo->beginTransaction();
        #INSERT INTO ORGANIZATION TABLE
        $stmt1 = $pdo->prepare(
        "INSERT INTO organizations ( id, name, subscription_id, billing_email, billing_address, phone, website) 
        VALUES (:id, :name, :subscription_id, :billing_email, :billing_address, :phone, :website)"
    );
        $stmt1->execute([
        ':id' => $organization_id,
        ':name' => $name,
        ':subscription_id' => $subscription_id,
        ':billing_email' => $billing_email,
        ':billing_address' => $billing_address,
        ':phone' => $phone_number,
        ':website' => $website     
    ]);
    $sql = "UPDATE users SET organization_id = :organization_id WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':organization_id', $organization_id); // Ensure $orgsArray is a PostgreSQL array string like '{uuid1,uuid2}'
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();

    $subStmt = $pdo->prepare("INSERT INTO subscriptions (
        id, organization_id, tier_id, renewal_date, payment_status,
        price) VALUES ( :id, :tier_id, NULL, 'active', 0)");
    $subStmt->execute([
        ':id' => $subscription_id,
        ':tier_id' => $tier_id,
    ]);
     #TODO commit data to database and send link to email address
    if ($pdo->commit() ){
        require_once __DIR__ . '/authenticate.php';
        respond([
            'status' => 'success',
            'message' => 'Organization created successfully',
            'token' => $jwt
        ], 200);
        exit;
    }else{
        respond(["status" => "error", "message" => "Unsuccessful"], 200);   
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    respond(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    $pdo->rollBack();
    respond(['error' => 'Error: ' . $e->getMessage()], 500);
}
    
