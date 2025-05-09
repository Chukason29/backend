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
    #TODO make sure email and password is inputted
    if (!isset($data['email'], $data['password'])) {
        respond(["status" => "false",'message' => 'All fields are required'], 400);
        exit;
    }
    #TODO
    if (!emailExists($pdo, $email)) {
        respond(["status" => "false", 'message' => 'Account does not exists'], 400);
        exit;
    }