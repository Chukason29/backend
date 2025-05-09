<?php
    #TODO
    // JSON body
    $data = json_decode(file_get_contents('php://input'), true);

    // Respond helper
    function respond($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    #TODO ==> Make sure email and password is inputted
    if (!isset($data['email'], $data['password'])) {
        respond(["status" => "false",'message' => 'All fields are required'], 400);
        exit;
    }

    /*$email = sanitizeInput($data['email']);
    $password = $data['password'];


    #TODO ==> Check if account exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !$user['is_active']) return "Account not active or doesn't exist";

    if (password_verify($password, $user['password_hash'])) {
        // Return session or token info
        return "Login successful";
    }*/