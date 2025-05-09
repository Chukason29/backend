<?php
    
    #TODO ==> check if the token is valid and not expired
    $email = validateTimedToken($verify_token);
    respond(["status" => "success","email" => $email], 200);exit;
    

    #TODO ==> If the token is valid and not expired, update the token table with true as the check column

    #TODO ==> If the token is valid and not expired, update the user table with true as the check column
    respond(["token" => $verify_token], 200);
    exit;
