<?php
    session_start();
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
    if ($_SESSION['user_id']) {
        respond(["status" => "success", 'user_id' => $_SESSION['user_id']], 400);
        exit;
    }
    /*if (!isset($data['email'], $data['password'])) {
        respond(["status" => "false",'message' => 'All fields are required'], 400);
        exit;
    }*/