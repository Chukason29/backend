<?php


require __DIR__ . '/../db.php';
require_once "./functions.php";
require_once __DIR__ . '/users/permissions.php';

ob_end_clean();


$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];


// Set CORS headers
//header("Access-Control-Allow-Origin: http://127.0.0.1:3001");
header("Access-Control-Allow-Origin: https://silent-coats-raise.loca.lt");
header("Access-Control-Allow-Credentials: true"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
//file_put_contents('log.txt', "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . PHP_EOL, FILE_APPEND);


// Handle OPTIONS preflight
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function respond($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

try {
    if ($method === 'POST' && $uri === '/api/register') {
        require_once __DIR__ . '/users/auth/registration.php';
    }
    if ($method === 'POST' && $uri === '/api/test') {
        require_once __DIR__ . '/users/auth/test.php';
    }
    if ($method === 'POST' && $uri === '/api/auth_access') {
        require_once __DIR__ . '/users/auth/auth_access.php';
    }
    if ($method === 'GET' && $uri === '/api/verify') {
        $verify_token = $_GET['token'] ?? null;
        if (empty($verify_token)) {
            respond(['error' => 'Token is required'], 400);
        }
        require_once __DIR__ . '/users/auth/verify.php';
        exit;
    }
    if ($method === 'POST' && $uri === '/api/login') {
        require_once __DIR__ . '/users/auth/login.php';
        exit;
    }
    if ($method === 'POST' && $uri === '/api/logout') {
        require_once __DIR__ . '/users/auth/logout.php';
        exit;
    }
    
    if ($method === 'POST' && $uri === '/api/users/add') {
        require_once __DIR__ . '/users/org_admin/add_user.php';
        exit;
    }
    if ($method === 'POST' && $uri === '/api/refresh_token') {
        require_once __DIR__ . '/users/auth/refresh_token.php';
        exit;
    }
    



    if ($method === 'POST' && $uri === '/api/roles') {
        require_once __DIR__ . '/super_admin/add_roles.php';
        exit;
    }
    if ($method === 'GET' && $uri === '/api/roles/list') {
        require_once __DIR__ . '/super_admin/get_roles.php';
        exit;
    }
    if ($method === 'POST' && $uri === '/api/tiers') {
        require_once __DIR__ . '/super_admin/add_tiers.php';
        exit;
    }
    if ($method === 'POST' && $uri === '/api/organization') {
        require_once __DIR__ . '/users/auth/organization.php';
        exit;
    }

    // Fallback
    respond(["status" =>"error", "message" => "Route not found"], 404);

} catch (PDOException $e) {
    respond(['error' => $e->getMessage()], 500);
}
