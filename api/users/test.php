<?php

// JSON body
$data = json_decode(file_get_contents('php://input'), true);

// Respond helper
function respond($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
if (!isset($data['name'], $data['email'])) {
    respond(["status" => "error",'message' => 'All fields are required'], 400);
}
respond(["status" => "sucess", "name" => $data['name'], 'email'=> $data['email']], 400);