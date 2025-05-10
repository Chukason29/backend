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
respond(["status" => "success",'message' => 'Working'], 400);
if (!isset($data['name'], $data['email'], $data['password'])) {
    respond(["status" => "false",'message' => 'All fields are required'], 400);
}