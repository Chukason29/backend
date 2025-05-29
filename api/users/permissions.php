<?php
function hasAccess($roleName, $apiRoute, $roles) {
    if (!isset($roles[$roleName])) {
        return false; // Role does not exist
    }

    return in_array($apiRoute, $roles[$roleName]['access']);
}

$roles = [
    'org_admin' => [
        'access' => ["/api/users/add", "/api/users/list", "/api/users/update", "/api/users/delete"]
    ],
    'manager' => [
        'access' => ['view_reports', 'update_settings']
    ],
    'user' => [
        'access' => ['view_reports']
    ],
    'guest' => [
        'access' => []
    ]
];
