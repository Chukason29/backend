<?php
// Step 1: Fetch JSON from external API
$json = file_get_contents('php://input');

// Step 2: Decode the JSON into an associative array
$data = json_decode($json, true);

// Step 3: Create a new array with only name and email
$result = [
    'status' => "success",
    'message' => "My name is ".$data['name']." and my email is ".$data['email'],
];

// Step 4: Encode it back to JSON and print
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?>
