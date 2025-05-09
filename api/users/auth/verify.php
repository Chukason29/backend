<?php

    #TODO ==> check if the token is valid and not expired
    $sql = "SELECT * FROM link_token WHERE token = :token AND check_column = false AND created_at > NOW() - INTERVAL 1 DAY";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $verify_token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tokenData) {
        respond(["error" => "Invalid or expired token"], 400);
    }
    respond(["status" => "success","token" => $tokenData], 200);
    #TODO ==> check if the token is already used or not

    #TODO ==> If the token is valid and not expired, update the token table with true as the check column

    #TODO ==> If the token is valid and not expired, update the user table with true as the check column
    respond(["token" => $verify_token], 200);
    exit;
