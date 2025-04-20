<?php
// Step 1: Fetch JSON from external API
$url = 'https://jsonplaceholder.typicode.com/users/1'; // Example API with name and email
$response = file_get_contents($url);

// Step 2: Decode the JSON into an associative array
$data = json_decode($response, true);

// Step 3: Create a new array with only name and email
$result = [
    'status' => "success",
    'message' => "My name is ".$data['name']." and my email is ".$data['email'],
];

// Step 4: Encode it back to JSON and print
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?>
