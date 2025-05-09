<?php
    
    #TODO ==> check if the token is valid and not expired
    $sql = "SELECT * FROM link_token WHERE token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':token', $verify_token, PDO::PARAM_STR);
   // $stmt->bindValue(':is_used', false, PDO::PARAM_BOOL); // ✅ This is correct
    $stmt->execute();
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tokenData) {
        respond(["status" => "false", "message"=> $tokenData], 400);
        exit;
    }else{
        
        respond(["data" => $tokenData], 200); exit;
    }
    respond(["status" => "success","data" => $tokenData], 200);
    

    #TODO ==> If the token is valid and not expired, update the token table with true as the check column

    #TODO ==> If the token is valid and not expired, update the user table with true as the check column
    respond(["token" => $verify_token], 200);
    exit;
