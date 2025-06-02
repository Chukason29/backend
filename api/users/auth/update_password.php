<?php
    session_start();
    #TODO
    // JSON body
    use Ramsey\Uuid\Uuid;
    $data = json_decode(file_get_contents('php://input'), true);

    // Respond helper

    #TODO ==> Make sure email and password is inputted
    if (!$_SESSION['user_id']) {
        respond(["status" => "error", 'message' => "unauthorized access"], 400);
        exit;
    }
    if (!isset($data['password'], $data['confirmed_password'])) {
        respond(["status" => "false",'message' => 'All fields are required'], 400);
        exit;
    }
    $password = $data["password"];
    $confirmed_password = $data["confirmed_password"];
    if (empty($password) || empty($confirmed_password)) {
        respond(["status" => "error", 'message' => 'Password is required'], 400);
        exit;
    }
    if( $password !== $confirmed_password) {
        respond(["status" => "error", 'message' => 'Passwords do not match'], 400);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    

    try {
        $pdo->beginTransaction();

        $sql = "UPDATE users SET password_hash = :hashedPassword WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':hashedPassword', $hashedPassword);
        $stmt->execute();

     #TODO commit data to database and send link to email address
    if ($pdo->commit() ){
        $_SESSION['organization_id'] = $organization_id;
        require_once __DIR__ . '/authenticate.php';
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
    
