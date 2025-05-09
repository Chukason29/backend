<?php

    #TODO ==> check if the token is valid and not expired
   
    $sql = "SELECT * FROM link_token WHERE token = :token AND is_used = :is_used AND created_at < NOW() - INTERVAL '48 hours'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':token', $verify_token, PDO::PARAM_STR);
    $stmt->bindValue(':is_used', true, PDO::PARAM_BOOL); // ✅ This is correct
    $stmt->execute();
    
    if ($tokenData) {
        respond(["status" => "false", "message"=> "Invalid or expired token"], 400);
        exit;
    }else{
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        respond([$tokenData], 200);
    }
    respond(["status" => "success","token" => $tokenData], 200);
    #TODO ==> check if the token is already used or not

    #TODO ==> If the token is valid and not expired, update the token table with true as the check column

    #TODO ==> If the token is valid and not expired, update the user table with true as the check column
    respond(["token" => $verify_token], 200);
    exit;
